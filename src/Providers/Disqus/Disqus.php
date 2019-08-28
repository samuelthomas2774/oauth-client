<?php

namespace OAuth2\Providers\Disqus;

use OAuth2\OAuth;
use OAuth2\AccessToken;
use OAuth2\UserProfile;
use OAuth2\UserProfilesInterface;

use OAuth2\AuthoriseEndpoint;
use OAuth2\TokenEndpoint;

use OAuth2\Grants\AuthorisationCodeGrant;
use OAuth2\Grants\AuthorisationCodeGrantInterface;

use OAuth2\Providers\Disqus\UserProfile as DisqusUserProfile;

class Disqus extends OAuth implements UserProfilesInterface, AuthorisationCodeGrantInterface
{
    use AuthoriseEndpoint;
    use TokenEndpoint;

    use AuthorisationCodeGrant;

    /**
     * Session prefix.
     *
     * @var string
     */
    public $session_prefix = 'disqus_';

    /**
     * Base API URL.
     *
     * @var string
     */
    public $base_api_endpoint = 'https://disqus.com/api/3.0/';

    /**
     * OAuth 2.0 authorise endpoint.
     *
     * @var string
     */
    public $authorise_endpoint = 'https://disqus.com/api/oauth/2.0/authorize/';

    /**
     * OAuth 2.0 token endpoint.
     *
     * @var string
     */
    public $token_endpoint = '/api/oauth/2.0/access_token/';

    /**
     * Scope separator.
     * This *should* be " " to be compliant with the OAuth 2.0 specification, however
     * some providers use "," instead.
     *
     * @var string
     */
    public $scope_separator = ',';

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
        if (!isset($options['query']) || !is_array($options['query'])) $options['query'] = [];

        $options['query']['api_key'] = $this->client_id;
        $options['query']['api_secret'] = $this->client_secret;

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
        if (!isset($options['query']) || !is_array($options['query'])) $options['query'] = [];

        $options['query']['api_key'] = $this->client_id;
        $options['query']['api_secret'] = $this->client_secret;

        return parent::authenticateAccessTokenToApiRequestOptions($method, $url, $options, $token);
    }

    /**
     * Returns the current user.
     *
     * @return \OAuth2\Providers\Disqus\UserProfile
     */
    public function getUserProfile(): UserProfile
    {
        $response = $this->api('GET', 'users/details.json');

        $user = new DisqusUserProfile(isset($response->response->id) ? $response->response->id : '');

        $user->response = $response;
        $user->username = $response->response->username;
        $user->name = $response->response->name;
        $user->url = $response->response->profileUrl;

        if (isset($response->response->email)) $user->email_addresses = [$response->response->email];

        return $user;
    }
}
