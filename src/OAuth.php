<?php

/**
 * Base OAuth 2.0 client class.
 */

namespace OAuth2;

use OAuth2\Exceptions\OAuthException;
use OAuth2\Exceptions\AccessDeniedException;
use OAuth2\Exceptions\InvalidRequestException;
use OAuth2\Exceptions\InvalidScopeException;
use OAuth2\Exceptions\ServerErrorException;
use OAuth2\Exceptions\TemporarilyUnavailableException;
use OAuth2\Exceptions\UnauthorisedClientException;
use OAuth2\Exceptions\UnsupportedResponseTypeException;
use OAuth2\Exceptions\InvalidClientException;
use OAuth2\Exceptions\InvalidGrantException;
use OAuth2\Exceptions\UnsupportedGrantTypeException;

use stdClass;
use Throwable;
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
    public $session_handler = 'OAuth2\\DefaultSessionHandler';
    // public $session_handler = null;

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
    public $token_endpoint = '/token';

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
     * The last generated state for each session prefix.
     *
     * @var array
     */
    protected static $state = [];

    /**
     * Creates a new OAuth client object.
     *
     * @param string $client_id
     * @param string $client_secret
     * @param \OAuth2\AccessToken|string $access_token
     * @param array $options
     */
    public function __construct(string $client_id, string $client_secret, $token = null, array $options = [])
    {
        $this->client_id = $client_id;
        $this->client_secret = $client_secret;

        if (array_key_exists('session_handler', $options)) $this->session_handler = $options['session_handler'];
        if (array_key_exists('session_prefix', $options)) $this->session_prefix = $options['session_prefix'];
        if (array_key_exists('base_api_endpoint', $options)) $this->base_api_endpoint = $options['base_api_endpoint'];
        if (array_key_exists('authorise_endpoint', $options)) $this->authorise_endpoint = $options['authorise_endpoint'];
        if (array_key_exists('token_endpoint', $options)) $this->token_endpoint = $options['token_endpoint'];
        if (array_key_exists('scope_separator', $options)) $this->scope_separator = $options['scope_separator'];
        if (array_key_exists('guzzle_options', $options)) $this->guzzle_options = $options['guzzle_options'];

        if ($token) $this->setAccessToken($token);
        elseif ($this->session('token') && $token !== false) $this->setAccessToken($this->session('token'));
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
        $client = new HttpClient($this->getGuzzleDefaultOptions());

        if ($auth instanceof AccessToken || is_string($auth)) {
            $options = $this->authenticateAccessTokenToApiRequestOptions($method, $url, $options, is_string($auth) ? new AccessToken($auth) : $auth);
        } elseif ($auth === true) {
            $options = $this->authenticateClientToApiRequestOptions($method, $url, $options);
        } elseif ($this->access_token && $auth !== false) {
            $options = $this->authenticateAccessTokenToApiRequestOptions($method, $url, $options, $this->access_token);
        }

        $response = $client->request($method, $url, $this->getGuzzleOptionsForRequest($method, $url, $options));

        if ($return_guzzle_response) {
            return $response;
        }

        return $this->getApiResponse($method, $url, $options, $response);
    }

    protected function getGuzzleDefaultOptions()
    {
        return array_merge([
            'base_uri' => $this->base_api_endpoint,
            'headers' => $this->api_headers,
        ], $this->guzzle_options);
    }

    protected function getGuzzleOptionsForRequest(string $method, string $url, array $options = [])
    {
        return $options;
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

        $options['headers']['Authorization'] = 'Bearer ' . $token->getAccessToken();

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
     * @param boolean $update_session
     * @return \OAuth2\AccessToken
     */
    public function getAccessTokenFromCode(string $code, string $redirect_url, array $requested_scope = [], bool $update_session = true): AccessToken
    {
        // Check if redirect_url is a url - the redirect_url should be exactly the same as the redirect_url used in the login dialog (so really, this should just be the same as the current url)
        if (!filter_var($redirect_url, FILTER_VALIDATE_URL)) {
            throw new TypeError('$redirect_url must be a valid URL.');
        }

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
            $token = $this->createAccessTokenFromSuccessfulResponse($response, $requested_scope);

            if ($update_session) $this->setAccessToken($token);

            return $token;
        } else {
            $this->handleErrorFromOAuthTokenResponse($response);
        }
    }

    /**
     * Creates an {@see OAuth2\AccessToken} object from a successful response from the token endpoint.
     *
     * @param mixed $response
     * @param array $requested_scope
     * @return \OAuth2\AccessToken
     */
    protected function createAccessTokenFromSuccessfulResponse($response, array $requested_scope = []): AccessToken
    {
        $refresh_token = isset($response->refresh_token) ? $response->refresh_token : null;
        $expires = isset($response->expires_in) ? time() + $response->expires_in : null;
        $scope = isset($response->scope) ? explode($this->scope_separator, $response->scope) : $requested_scope;

        return new AccessToken($response->access_token, $refresh_token, $expires, $scope);
    }

    // https://tools.ietf.org/html/rfc6749#section-5.2
    protected function handleErrorFromOAuthTokenResponse($response, Throwable $previous = null)
    {
        switch ($response->error) {
            default:
                throw OAuthException::fromResponse($response, $previous);
            case 'invalid_request':
                throw InvalidRequestException::fromResponse($response, $previous);
            case 'invalid_client':
                throw InvalidClientException::fromResponse($response, $previous);
            case 'invalid_grant':
                throw InvalidGrantException::fromResponse($response, $previous);
            case 'unauthorized_client':
                throw UnauthorisedClientException::fromResponse($response, $previous);
            case 'unsupported_grant_type':
                throw UnsupportedGrantTypeException::fromResponse($response, $previous);
            case 'invalid_scope':
                throw InvalidScopeException::fromResponse($response, $previous);
        }
    }

    public function getAccessTokenFromRequestCode(string $redirect_url, array $requested_scope = [], bool $update_session = true)
    {
        if (isset($_GET['error'])) {
            $this->handleErrorFromOAuthAuthoriseRequest($_GET);
        }

        if (!isset($_GET['code']) || !isset($_GET['state'])) {
            throw new Exception('Missing code and state.');
        }

        $state = $this->session('state');
        if (empty($state) || $state !== $_GET['state']) {
            throw new Exception('Invalid state.');
        }

        return $this->getAccessTokenFromCode($_GET['code'], $redirect_url, $requested_scope, $update_session);
    }

    // https://tools.ietf.org/html/rfc6749#section-4.1.2.1
    protected function handleErrorFromOAuthAuthoriseRequest(array $request, Throwable $previous = null)
    {
        switch ($request['error']) {
            default:
                throw OAuthException::fromRequest($request, $previous);
            case 'invalid_request':
                throw InvalidRequestException::fromRequest($request, $previous);
            case 'unauthorized_client':
                throw UnauthorisedClientException::fromRequest($request, $previous);
            case 'access_denied':
                throw AccessDeniedException::fromRequest($request, $previous);
            case 'unsupported_response_type':
                throw UnsupportedResponseTypeException::fromRequest($request, $previous);
            case 'invalid_scope':
                throw InvalidScopeException::fromRequest($request, $previous);
            case 'server_error':
                throw ServerErrorException::fromRequest($request, $previous);
            case 'temporarily_unavailable':
                throw TemporarilyUnavailableException::fromRequest($request, $previous);
        }
    }

    /**
     * Exchanges a refresh token for an access token.
     *
     * @param \OAuth2\AccessToken|string $refresh_token
     * @return \stdClass
     */
    public function getAccessTokenFromRefreshToken($refresh_token): AccessToken
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
            $this->handleErrorFromOAuthTokenResponse($response);
        }
    }

    /** Resource Owner Credentials Grant */

    /**
     * Exchanges a username and password for an access token.
     *
     * @param string $username
     * @param string $password
     * @return \OAuth2\AccessToken
     */
    public function getAccessTokenFromUserCredentials(string $username, string $password): AccessToken
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
            $this->handleErrorFromOAuthTokenResponse($response);
        }
    }

    /** Client Credentials Grant */

    /**
     * Exchanges the client credentials for an access token.
     *
     * @return \OAuth2\AccessToken
     */
    public function getAccessTokenFromClientCredentials(): AccessToken
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
            $this->handleErrorFromOAuthTokenResponse($response);
        }
    }

    /**
     * Generate a URL to redirect users to to authorise this client.
     *
     * @param string $state
     * @param string $redirect_url
     * @param array $scope
     * @param array $params
     * @return string
     */
    public function generateAuthoriseUrl(string $state = null, string $redirect_url = null, array $scope = [], array $params = []): string
    {
        // Check if redirect_url is a url - the redirect_url should go to a PHP script on the same domain that runs OAuth2::getAccessTokenFromCode()
        if (!filter_var($redirect_url, FILTER_VALIDATE_URL)) throw new Exception('$redirect_url must be a valid URL.');

        $default_params = [
            'response_type' => 'code',
            'client_id' => $this->client_id,
            'redirect_uri' => $redirect_url,
            'scope' => implode($this->scope_separator, $scope),
            'state' => $state,
        ];

        return $this->authorise_endpoint . (strpos($this->authorise_endpoint, '?') !== false ? '&' : '?')
            . http_build_query(array_merge($default_params, $params));
    }

    /**
     * Generate a URL to redirect users to to authorise this client with a state.
     *
     * @param string $redirect_url
     * @param array $scope
     * @param array $params
     * @return string
     */
    public function generateAuthoriseUrlAndState(string $redirect_url = null, array $scope = [], array $params = []): string
    {
        // Generate a unique state parameter and store it in the session
        $state = $this->getState();

        return $this->generateAuthoriseUrl($state, $redirect_url, $scope, $params);
    }

    /**
     * Generate a state.
     *
     * @param boolean $update_session
     * @return string
     */
    public function getState(bool $update_session = true): string
    {
        if (array_key_exists($this->session_prefix, self::$state)) {
            return self::$state[$this->session_prefix];
        }

        $state = hash('sha256', time() . uniqid(mt_rand(), true));
        $this->session('state', $state);

        return self::$state[$this->session_prefix];
    }

    /**
     * Redirect the user to the URL to authorise this client.
     *
     * @param string $redirect_url
     * @param array $scope
     * @param array $params
     */
    public function redirectToAuthoriseEndpoint(string $redirect_url = null, array $scope = [], array $params = [])
    {
        $url = $this->generateAuthoriseUrlAndState($redirect_url, $scope, $params);

        // Make sure headers have not been sent
        if (headers_sent()) throw new Exception('Headers have already been sent.');

        // Redirect to the Login Dialog
        header('Location: ' . $url, true, 303);
    }

    public function generateImplicitAuthoriseUrl(string $redirect_url = null, array $scope = [], array $params = []): string
    {
        if (!isset($params['response_type'])) $params['response_type'] = 'token';

        return $this->generateAuthoriseUrl(null, $redirect_url, $scope, $params);
    }

    public function redirectToImplicitAuthoriseEndpoint(string $redirect_url = null, array $scope = [], array $params = []): string
    {
        if (!isset($params['response_type'])) $params['response_type'] = 'token';

        return $this->redirectToAuthoriseEndpoint(null, $redirect_url, $scope, $params);
    }

    public function getAccessToken()
    {
        return $this->access_token;
    }

    public function setAccessToken($token, bool $update_session = true)
    {
        if (is_string($token)) $token = new AccessToken($token);

        if (!$token instanceof AccessToken && $token !== null) throw new TypeError('$token must be an \OAuth2\AccessToken object, a string or null.');

        $this->access_token = $token;

        if ($update_session) $this->session('token', $token);
    }

    /**
     * Returns the current session handler.
     *
     * @return \OAuth2\SessionHandlerInterface
     */
    public function getSessionHandler(): SessionHandlerInterface
    {
        if (is_string($this->session_handler)) {
            $this->session_handler = new $this->session_handler();
        }

        return $this->session_handler;
    }

    /**
     * Check if sessions are enabled.
     *
     * @return boolean
     */
    public function sessions(): bool
    {
        // No session handler
        if (!$this->session_handler) return false;

        return call_user_func([$this->getSessionHandler(), 'enabled']);
    }

    /**
     * Get or set session data.
     * Fails silently if sessions are disabled.
     *
     * @param string $key
     * @param mixed $value
     * @return mixed
     */
    public function session(string $name, $value = null)
    {
        // Check if sessions are enabled
        if (!$this->sessions()) return;

        if ((func_num_args() >= 2) && $value === null) {
            // Delete
            call_user_func([$this->getSessionHandler(), 'delete'], $this->session_prefix . $name);
        } elseif (func_num_args() >= 2) {
            // Set
            call_user_func([$this->getSessionHandler(), 'set'], $this->session_prefix . $name, $value);
        } else {
            // Get
            return call_user_func([$this->getSessionHandler(), 'get'], $this->session_prefix . $name);
        }
    }
}
