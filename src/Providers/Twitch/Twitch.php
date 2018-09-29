<?php

namespace OAuth2\Providers\Twitch;

use OAuth2\OAuth;
use OAuth2\AccessToken;
use OAuth2\UserProfile;
use OAuth2\UserProfilesInterface;

use OAuth2\Providers\Twitch\UserProfile as TwitchUserProfile;

class Twitch extends OAuth implements UserProfilesInterface
{
    /**
     * Session prefix.
     *
     * @var string
     */
    public $session_prefix = 'twitch_';

    /**
     * Base API URL.
     *
     * @var string
     */
    public $base_api_endpoint = 'https://api.twitch.tv/helix/';

    /**
     * OAuth 2.0 authorise endpoint.
     *
     * @var string
     */
    public $authorise_endpoint = 'https://api.twitch.tv/kraken/oauth2/authorize';

    /**
     * OAuth 2.0 token endpoint.
     *
     * @var string
     */
    public $token_endpoint = '/kraken/oauth2/token';

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
        $scope = isset($response->scope) ? $response->scope : $requested_scope;

        $token = new AccessToken($response->access_token, $refresh_token, $expires, $scope);
        $token->response = $response;
        $token->requested_scope = $requested_scope;
        return $token;
    }

    /**
     * Returns the current user.
     *
     * @return \OAuth2\Providers\Twitch\UserProfile
     */
    public function getUserProfile(): UserProfile
    {
        $response = $this->api('GET', 'users');

        $user = new TwitchUserProfile($response->data[0]->id);

        $user->response = $response;
        $user->username = $response->data[0]->login;
        $user->name = $response->data[0]->display_name;

        return $user;
    }
}
