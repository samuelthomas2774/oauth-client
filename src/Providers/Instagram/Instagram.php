<?php

namespace OAuth2\Providers\Instagram;

use OAuth2\OAuth;
use OAuth2\UserProfilesInterface;
use OAuth2\UserProfile;

use OAuth2\Providers\Instagram\UserProfile as InstagramUserProfile;

class Instagram extends OAuth implements UserProfilesInterface
{
    /**
     * Session prefix.
     *
     * @var string
     */
    public $session_prefix = 'instagram_';

    /**
     * Base API URL.
     *
     * @var string
     */
    public $base_api_endpoint = 'https://api.instagram.com/v1/';

    /**
     * OAuth 2.0 authorise endpoint.
     *
     * @var string
     */
    public $authorise_endpoint = 'https://api.instagram.com/oauth/authorize/';

    /**
     * OAuth 2.0 token endpoint.
     *
     * @var string
     */
    public $token_endpoint = '/oauth/access_token';

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

        $options['query']['access_token'] = $token->getAccessToken();

        return $options;
    }

    /**
     * Returns the current user.
     *
     * @return \OAuth2\Providers\Instagram\UserProfile
     */
    public function getUserProfile(): UserProfile
    {
        $response = $this->api('GET', 'users/self/');

        $user = new InstagramUserProfile(isset($response->data->id) ? $response->data->id : '');

        $user->response = $response;
        $user->name = $response->data->name;
        $user->email_addresses = [$response->data->email];

        return $user;
    }
}
