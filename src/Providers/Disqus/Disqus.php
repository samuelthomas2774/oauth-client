<?php

namespace OAuth2\Providers\Disqus;

use OAuth2\OAuth;
use OAuth2\UserProfilesInterface;
use OAuth2\UserProfile;

use OAuth2\Providers\Disqus\UserProfile as DisqusUserProfile;

class Disqus extends OAuth implements UserProfilesInterface
{
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
     * Returns the current user.
     *
     * @return \OAuth2\Providers\Disqus\UserProfile
     */
    public function getUserProfile(): UserProfile
    {
        $response = $this->api('GET', 'users/details.json');

        $user = new DisqusUserProfile(isset($response->response->id) ? $response->response->id : '');

        $user->response = $response;
        $user->name = $response->response->name;
        $user->email_addresses = [$response->response->email];

        return $user;
    }
}
