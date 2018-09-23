<?php

/**
 * Base OAuth client class
 */

namespace OAuth2;

use stdClass;
use Exception;
use TypeError;

use GuzzleHttp\Client as HttpClient;
use Psr\Http\Message\ResponseInterface;

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
     * @var \OAuth2\SessionHandlerInterface|string
     */
    // public $session_handler = 'OAuth2\\DefaultSessionHandler';
    public $session_handler = null;

    /**
     * Default headers to send to the API.
     *
     * @var array
     */
    public $api_headers = [
        'User-Agent' => 'OAuth 2.0 Client https://github.com/samuelthomas2774/oauth-client',
    ];

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
     * Options to pass to Guzzle when sending requests.
     *
     * @var array
     */
    public $guzzle_options = [];

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
     * @param array $options
     * @param boolean|\OAuth2\AccessToken|string $auth If true the request will be authenticated with the client ID and secret, if an \OAuth2\AccessToken or string the request will be authenticated with that access token, if false the request will not be authenticated, if null the request will be authenticated with the current access token
     * @return object
     */
    public function api(string $method, string $url, array $options = [], $auth = null, bool $return_guzzle_response = false)
    {
        $client = new HttpClient(array_merge([
            'base_uri' => $this->base_api_endpoint,
        ], $this->guzzle_options));

        if ($auth instanceof AccessToken || is_string($auth)) {
            $options = $this->authenticateAccessTokenToApiRequestOptions($method, $url, $options, is_string($auth) ? new AccessToken($auth) : $auth);
        } elseif ($auth === true) {
            $options = $this->authenticateClientToApiRequestOptions($method, $url, $options);
        } elseif ($this->access_token && $auth !== false) {
            $options = $this->authenticateAccessTokenToApiRequestOptions($method, $url, $options, $this->access_token);
        }

        $response = $client->request($method, $url, $options);

        if ($return_guzzle_response) {
            return $response;
        }

        return $this->getApiResponse($method, $url, $options, $response);
    }

    /**
     * Returns the request options with an Authorization header with the client ID and secret.
     *
     * @param string $method
     * @param string $url
     * @param array $options
     * @return array $options
     */
    protected function authenticateClientToApiRequestOptions(string $method, string $url, array $options): array
    {
        if (!isset($options['headers']) || !is_array($options['headers'])) $options['headers'] = [];

        $options['headers']['Authorization'] = 'Basic ' . base64_encode($this->client_id . ':' . $this->client_secret);

        return $options;
    }

    /**
     * Returns the request options with an Authorization header with the access token.
     *
     * @param string $method
     * @param string $url
     * @param array $options
     * @param \OAuth2\AccessToken $token
     * @return array $options
     */
    protected function authenticateAccessTokenToApiRequestOptions(string $method, string $url, array $options, AccessToken $token): array
    {
        if (!isset($options['headers']) || !is_array($options['headers'])) $options['headers'] = [];

        $options['headers']['Authorization'] = 'Bearer ' . $token->access_token;

        return $options;
    }

    protected function getApiResponse(string $method, string $url, array $options, ResponseInterface $response)
    {
        return json_decode($response->getBody());
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

        $response = $this->api('POST', $this->token_endpoint, [
            'form_params' => [
                'grant_type' => 'authorization_code',
                'client_id' => $this->client_id,
                'client_secret' => $this->client_secret,
                'redirect_uri' => $redirect_url,
                'code' => $code,
            ],
        ], false);

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

        $response = $this->api('POST', $this->token_endpoint, [
            'form_params' => [
                'grant_type' => 'refresh_token',
                'client_id' => $this->client_id,
                'client_secret' => $this->client_secret,
                'refresh_token' => $refresh_token,
            ]
        ], false);

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
        $response = $this->api('POST', $this->token_endpoint, [
            'form_params' => [
                'grant_type' => 'password',
                'client_id' => $this->client_id,
                'client_secret' => $this->client_secret,
                'username' => $username,
                'password' => $password,
            ]
        ], false);

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
        $response = $this->api('POST', $this->token_endpoint, [
            'form_params' => [
                'grant_type' => 'client_credentials',
                'client_id' => $this->client_id,
                'client_secret' => $this->client_secret,
            ],
        ], false);

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

        $url_params = [
            'response_type' => 'code',
            'client_id' => $this->client_id,
            'redirect_uri' => $redirect_url,
            'scope' => implode($this->scope_separator, $scope),
            'state' => $state,
        ];

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
        header('Location: ' . $url, true, 303);
    }
}
