<?php

/**
 * Base OAuth 2.0 client class.
 */

namespace OAuth2;

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
    protected $client_id = null;

    /**
     * The client secret.
     *
     * @var string
     */
    protected $client_secret = null;

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
     * The last generated state for each session prefix.
     *
     * @var array
     */
    protected static $last_state = [];

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

        if ($this instanceof MultipleInstancesInterface && array_key_exists('instance_url', $options)) {
            $this->setInstanceUrl($options['instance_url']);
        }

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

    protected function getGuzzleDefaultOptions(): array
    {
        return array_merge([
            'base_uri' => $this->base_api_endpoint,
            'headers' => $this->api_headers,
        ], $this->guzzle_options);
    }

    protected function getGuzzleOptionsForRequest(string $method, string $url, array $options = []): array
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

    public function getAccessToken(): ?AccessToken
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
    public function getSessionHandler(): ?SessionHandlerInterface
    {
        if (is_string($this->session_handler)) {
            $this->session_handler = new $this->session_handler();
        }

        return $this->session_handler;
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
        $session_handler = $this->getSessionHandler();

        // Check if sessions are enabled
        if (!$session_handler || !$session_handler->enabled()) return;

        if ((func_num_args() >= 2) && $value === null) {
            // Delete
            $session_handler->delete($this->session_prefix . $name);
        } elseif (func_num_args() >= 2) {
            // Set
            $session_handler->set($this->session_prefix . $name, $value);
        } else {
            // Get
            return $session_handler->get($this->session_prefix . $name);
        }
    }

    public function __debugInfo()
    {
        $debug_info = (array)$this;

        $debug_info["\0*\0client_secret"] = null;
        $debug_info["\0OAuth2\\OAuth\0access_token"] = null;

        return $debug_info;
    }
}
