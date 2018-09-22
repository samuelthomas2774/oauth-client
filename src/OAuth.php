<?php

/**
 * Base OAuth client class
 */

namespace OAuth2;

use stdClass;
use Exception;
use TypeError;

class OAuth
{
    /**
     * The client ID.
     *
     * @var string
     */
    private $client_id = null;

    /**
     * The client secret.
     *
     * @var string
     */
    private $client_secret = null;

    /**
     * The current access token.
     *
     * @var string
     */
    private $access_token = null;

    /**
     * Session prefix.
     *
     * @var string
     */
    public $session_prefix = 'oauth_';

    /**
     * Session handler object.
     * If this is a string, an object of that class will be created.
     * If this is null, sessions will be disabled.
     *
     * @var \OAuth2\SessionHandlerInterface
     */
    // public $session_handler = 'OAuth2\\DefaultSessionHandler';
    public $session_handler = null;

    /**
     * Default headers to send to the API.
     *
     * @var array
     */
    public $api_headers = array(
        'User-Agent'            => 'OAuth 2.0 Client https://github.com/samuelthomas2774/oauth-client'
    );

    /**
     * Base API URL.
     *
     * @var string
     */
    public $base_api_endpoint = 'https://example.com/api';

    /**
     * OAuth 2.0 authorise endpoint.
     *
     * @var string
     */
    public $authorise_endpoint = 'https://example.com/authorise';

    /**
     * OAuth 2.0 token endpoint.
     *
     * @var string
     */
    public $token_endpoint = 'https://example.com/token';

    /**
     * Scope separator.
     * This *should* be " " to be compliant with the OAuth 2.0 specification, however
     * some providers use "," instead.
     *
     * @var string
     */
    public $scope_separator = ' ';

    /**
     * The last request's error.
     */
    public $error = null;

    /** Request constants */
    const GET = 'GET';
    const POST = 'POST';
    const PUT = 'PUT';
    const DELETE = 'DELETE';

    const responseText = 10;
    const responseJSONArray = 21;
    const responseJSONObject = 22;
    const responseQueryStringArray = 31;
    const responseQueryStringObject = 32;
    const responseXMLArray = 41;
    const responseXMLObject = 42;
    const responseSimpleXMLObject = 43;

    /**
     * Creates a new OAuth client object.
     *
     * @param string $client_id
     * @param string $client_secret
     * @param string $access_token
     */
    public function __construct(string $client_id, string $client_secret, string $access_token = null)
    {
        $this->client_id = $client_id;
        $this->client_secret = $client_secret;
        $this->access_token = $access_token;
    }

    /**
     * Creates a new request to the provider's API.
     *
     * @param string $method
     * @param string $url
     * @param array $params
     * @param array $headers
     * @param boolean $auth
     * @return \OAuth2\Request
     */
    public function api(string $method, string $url, array $params = array(), array $headers = array(), boolean $auth = false)
    {
        // Everything here is done by the OAuth2\Request class
        return new Request($this, $method, $url, $params, $headers, $auth);
    }

    /** Authorisation Code Grant */

    /**
     * Exchanges a code for an access token.
     *
     * @param string $code
     * @param string $redirect_url
     * @param array $requested_scope The requested scope to use in the {@see OAuth2\AccessToken} object if none is available
     * @return \OAuth2\AccessToken
     */
    public function getAccessTokenFromCode(string $code, string $redirect_url, array $requested_scope = null)
    {
        // Check if redirect_url is a url - the redirect_url should be exactly the same as the redirect_url used in the login dialog (so really, this should just be the same as the current url)
        if (!filter_var($redirect_url, FILTER_VALIDATE_URL)) throw new TypeError('$redirect_url must be a valid URL.');

        $request = $this->api(OAuth::POST, $this->token_endpoint, array(
            'grant_type'            => 'authorization_code',
            'client_id'             => $this->client_id,
            'client_secret'         => $this->client_secret,
            'redirect_uri'          => $redirect_url,
            'code'                  => $code
        ), null, true);

        $request->execute();
        $response = $request->responseObject();

        if (isset($response->access_token)) {
            return $this->createAccessTokenFromSuccessfulResponse($response);
        } else {
            throw new Exception($response);
        }
    }

    /**
     * Creates an {@see OAuth2\AccessToken} object from a successful response from the token endpoint.
     *
     * @param \stdClass $response
     * @param array $requested_scope
     * @return \OAuth2\AccessToken
     */
    public function createAccessTokenFromSuccessfulResponse(stdClass $response, array $requested_scope = null)
    {
        $refresh_token = isset($response->refresh_token) ? $response->refresh_token : null;
        $expires = isset($response->expires_in) ? time() + $response->expires_in : null;
        $scope = isset($response->scope) ? explode($this->scope_separator, $response->scope) : $requested_scope;

        return new AccessToken($response->access_token, $refresh_token, $expires, $scope);
    }

    /**
     * Exchanges a refresh token for an access token.
     *
     * @param \OAuth2\AccessToken|string $refresh_token
     * @return \stdClass
     */
    public function getAccessTokenFromRefreshToken($refresh_token)
    {
        if ($refresh_token instanceof AccessToken) $refresh_token = $refresh_token->getRefreshToken();
        if (!is_string($refresh_token)) throw new TypeError('$refresh_token must be an OAuth2\AccessToken object with a refresh token or a string.');

        $request = $this->api(OAuth2::POST, $this->token_endpoint, array(
            'grant_type'            => 'refresh_token',
            'client_id'             => $this->client_id,
            'client_secret'         => $this->client_secret,
            'refresh_token'         => $refresh_token
        ), null, true);

        $request->execute();
        $response = $request->responseObject();

        if (isset($response->access_token)) {
            return $this->createAccessTokenFromSuccessfulResponse($response);
        } else {
            throw new Exception($response);
        }
    }

    /** Implicit Grant */

    // function iloginURL(): Returns the URL for the login dialog
    public function iloginURL($redirect_url, $permissions = array(), $params = array())
    {
        if (is_array($params) && !isset($params['response_type'])) $params['response_type'] = 'token';
        return $this->loginURL($redirect_url, $permissions, $params);
    }

    // function iloginButton(): Returns the URL for the login dialog
    public function iloginButton($button_text, $redirect_url, $permissions = array(), $params = array(), $colour = null)
    {
        if (is_array($params) && !isset($params['response_type'])) $params['response_type'] = 'token';
        return $this->loginButton($button_text, $redirect_url, $permissions, $params, $colour);
    }

    // function iloginRedirect(): Redirects to the login dialog
    public function iloginRedirect($redirect_url, $permissions = array(), $params = array(), $message = '')
    {
        if (is_array($params) && !isset($params['response_type'])) $params['response_type'] = 'token';
        return $this->loginRedirect($redirect_url, $permissions, $params, $message);
    }

    /** Resource Owner Credentials Grant */

    /**
     * Exchanges a username and password for an access token.
     *
     * @param string $username
     * @param string $password
     * @return \stdClass
     */
    public function getAccessTokenFromUserCredentials(string $username, string $password)
    {
        $request = $this->api(OAuth2::POST, $this->token_endpoint, array(
            'grant_type'            => 'password',
            'client_id'             => $this->client_id,
            'client_secret'         => $this->client_secret,
            'username'              => $username,
            'password'              => $password
        ), null, true);

        $request->execute();
        $response = $request->responseObject();

        if (isset($response->access_token)) {
            return $this->createAccessTokenFromSuccessfulResponse($response);
        } else {
            throw new Exception($response);
        }
    }

    /** Client Credentials Grant */

    /**
     * Exchanges the client credentials for an access token.
     *
     * @return \stdClass
     */
    public function getAccessTokenFromClientCredentials()
    {
        $request = $this->api(OAuth2::POST, $this->token_endpoint, array(
            'grant_type'            => 'client_credentials',
            'client_id'             => $this->client_id,
            'client_secret'         => $this->client_secret
        ), null, true);

        $request->execute();
        $response = $request->responseObject();

        if (isset($response->access_token)) {
            return $this->createAccessTokenFromSuccessfulResponse($response);
        } else {
            throw new Exception($response);
        }
    }

    /**
     * Generate the URL to redirect users to to authorise this client.
     *
     * @param string $state
     * @param string $redirect_url
     * @param array $scope
     * @param array $params
     */
    public function generateLoginURL(string $state = null, string $redirect_url = null, array $scope = array(), array $params = array())
    {
        // Check if redirect_url is a url - the redirect_url should go to a PHP script on the same domain that runs OAuth2::getAccessTokenFromCode()
        if (!filter_var($redirect_url, FILTER_VALIDATE_URL)) throw new Exception('$redirect_url must be a valid URL.');

        $url_params = array(
            'response_type'         => 'code',
            'client_id'             => $this->client_id,
            'redirect_uri'          => $redirect_url,
            'scope'                 => implode($this->scope_separator, $scope),
            'state'                 => $state
        );

        $url = $this->authorise_endpoint . '?' . http_build_query(array_merge($params, $url_params));
        return $url;
    }

    public function generateLoginURLAndState(string $redirect_url = null, array $scope = array(), array $params = array())
    {
        // Generate a unique state parameter and store it in the session
        $state = hash('sha256', time() . uniqid(mt_rand(), true));
        $this->session('state', $state);

        return $this->generateLoginURL($state, $redirect_url, $scope, $params);
    }

    /**
     * Redirect the user to the URL to authorise this client.
     *
     * @param string $redirect_url
     * @param array $scope
     * @param array $params
     */
    public function redirectToAuthoriseEndpoint(string $redirect_url = null, string $scope = array(), $params = array())
    {
        // Get a Login Dialog URL using the OAuth2::loginURL() function
        $url = $this->generateLoginURLAndState($redirect_url, $scope, $params);

        // Make sure headers have not been sent
        if (headers_sent()) throw new Exception('Headers have already been sent.');

        // Redirect to the Login Dialog
        header("Location: {$url}", true, 303);
    }

    /**
     * Get or set the current access token.
     *
     * @param string $access_token
     * @param boolean $session
     */
    public function accessToken($token = false, $session = true)
    {
        if ($token === null) {
            $this->token = null;
            if ($session === true) $this->session('token', null);
        } elseif (is_string($token)) {
            $this->token = $token;
            if ($session === true) $this->session('token', $token);
        } else {
            return $this->token;
        }
    }

    // function options(): Returns / sets an option
    // Get: $oauth->options("session_prefix");
    // Get: $oauth->options(Array("dialog", "base_url"));
    // Get: $oauth->options([ "dialog", "base_url" ]);
    // Set: $oauth->options("session_prefix", "oauth_");
    // Set: $oauth->options(Array("dialog", "base_url"), "https://www.facebook.com/dialog/oauth");
    // Set: $oauth->options([ "dialog", "base_url" ], "https://www.facebook.com/dialog/oauth");
    // Set: $oauth->options(Array("api", "headers"), Array("X-Header" => "X-Value")); // Array will be merged.
    /**
     * @deprecated
     */
    public function options($name) {
        $params = func_get_args();
        if(is_string($name) || is_int($name)) $name = Array($name);
        if(!is_array($name)) return null;
        $aset = array_key_exists(1, $params) ? true : false;

        $options = Array(&$this->options);
        $ek = 0;
        foreach($name as $i => $key) {
            if(is_object($options[$ek])) {
                if(!isset($options[$ek]->{$key}) && $aset) {
                    $options[$ek]->{$key} = new stdClass();
                    $options[$ek + 1] = &$options[$ek]->{$key};
                } elseif(!isset($options[$ek]->{$key}) && !$aset) $options[$ek + 1] = null;
                else $options[$ek + 1] = &$options[$ek]->{$key};
            } elseif(is_array($options[$ek])) {
                if(!isset($options[$ek][$key]) && $aset) {
                    $options[$ek][$key] = Array();
                    $options[$ek + 1] = &$options[$ek][$key];
                } elseif(!isset($options[$ek][$key]) && !$aset) $options[$ek + 1] = null;
                else $options[$ek + 1] = &$options[$ek][$key];
            } else {
                $options[$ek + 1] = &$options[$ek];
            }
            $ek++;
        }
        $option = &$options[$ek];

        if($aset && !array_key_exists(2, $params)) {
            $value = $params[1];

            /*if(is_object($option) && (is_object($value) || is_array($value))) {
                foreach($value as $k => $v) {
                    if(is_object($v)) $option->{$k} = (object)array_merge((array)$option->{$k}, (array)$v);
                    if(is_array($v)) $option->{$k} = (array)array_merge((array)$option->{$k}, (array)$v);
                    else $option->{$k} = $v;
                }
            } elseif(is_array($option) && (is_object($value) || is_array($value))) {
                foreach($value as $k => $v) {
                    if(is_object($v)) $option[$k] = (object)array_merge((array)$option[$k], (array)$v);
                    if(is_array($v)) $option[$k] = (array)array_merge((array)$option[$k], (array)$v);
                    else $option[$k] = $v;
                }
            } else*/ $option = $value;
        } else {
            return $option;
        }
    }

    // function defaultoptions(): Returns the default options
    /**
     * @deprecated
     */
    public function defaultoptions() {
        $options = new stdClass();
        $options->session_prefix = 'oauth_';
        $options->button_colour = 'rgb(47,71,122)';

        $options->session_handler = new stdClass();
        $options->session_handler->check = 'OAuth2::_session_check';
        $options->session_handler->get = 'OAuth2::_session_get';
        $options->session_handler->set = 'OAuth2::_session_set';
        $options->session_handler->delete = 'OAuth2::_session_delete';

        // Login Dialog: Set a few important variables for using the Login Dialog
        $options->dialog = new stdClass();
        $options->dialog->base_url = 'https://mydatastore.com/oauth/authorize';
        $options->dialog->scope_separator = ' ';

        // API: Set a few important variables for using the API
        // token_auth: 1 = access_token parameter (default), 2 = Authorization header, false = Do not automatically send an access token
        $options->api = new stdClass();
        $options->api->base_url = 'https://api.mydatastore.com';
        $options->api->token_auth = true;
        $options->api->headers = Array(
            'User-Agent' => 'OAuth 2.0 Client https://github.com/samuelthomas2774/oauth-client'
        );
        $options->api->callback = null;

        // Default requests: Sets a few important variables for the requests this class makes
        $options->requests = new stdClass();
        $options->requests->{'/oauth/token'} = '/oauth/token';
        $options->requests->{'/oauth/token:response'} = 'json';
        $options->requests->{'/oauth/token/debug'} = '/oauth/token/debug';

        // Errors: Sets how and when this class triggers errors
        // Invalid parameter exceptions are thrown even if throw is set to false here
        $options->errors = new stdClass();
        $options->errors->throw = true;

        return $options;
    }

    /**
     * Get the client ID and secret.
     *
     * @return \stdClass
     */
    public function client()
    {
        return (object)$this->client;
    }

    /**
     * @deprecated
     */
    public function triggerError($message, $error = null) {
        $this->error = $error !== null ? $error : $message;
        if($this->options([ 'errors', 'throw' ]) === true) throw new Exception($message);
    }

    /**
     * Check if sessions are enabled.
     */
    public function sessions(&$prefix = null)
    {
        // Get session_prefix - if not a string or false reset to default
        if (!is_string($prefix = $this->options([ 'session_prefix' ])) && ($prefix !== false))
            $this->options('session_prefix', $prefix = $this->defaultoptions()->session_prefix);

        if (!call_user_func($this->options([ 'session_handler', 'check' ]), $this))
            // Doesn't matter if sessions are disabled: one hasn't been started
            return false;
        elseif($prefix === false)
            // Sessions are diabled
            return false;
        else
            // Sessions are enabled and one is active
            return true;
    }

    /**
     * Get or set session data.
     * Fails silently if sessions are disabled.
     *
     * @param string $key
     * @param $value
     * @return
     */
    public function session($name, $value = null)
    {
        // Check if sessions are enabled
        if (!$this->sessions($session_prefix)) return null;

        if ((func_num_args() >= 2) && ($value === null))
            // Delete
            call_user_func($this->options([ 'session_handler', 'delete' ]), $session_prefix . $name, $this);
        elseif (func_num_args() >= 2)
            // Set
            call_user_func($this->options([ 'session_handler', 'set' ]), $session_prefix . $name, $value, $this);
        else
            // Get
            return call_user_func($this->options([ 'session_handler', 'get' ]), $session_prefix . $name, $this);
    }

    /**
     * @deprecated
     */
    public function sessionDelete($name) {
        // Check if sessions are enabled.
        if(!$this->sessions()) return null;
        $session_prefix = $this->options([ 'session_prefix' ]);

        if(isset($_SESSION[$session_prefix . $name]))
            // Delete
            unset($_SESSION[$session_prefix . $name]);
    }
}
