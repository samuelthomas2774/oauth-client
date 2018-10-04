<?php

namespace OAuth2\Providers\Pinterest;

use OAuth2\OAuth;
use OAuth2\AccessToken;
use OAuth2\UserProfile;
use OAuth2\UserProfilesInterface;

use OAuth2\AuthoriseEndpoint;
use OAuth2\TokenEndpoint;
use OAuth2\AuthoriseEndpointInterface;
use OAuth2\TokenEndpointInterface;

use OAuth2\Grants\AuthorisationCodeGrant;
use OAuth2\Grants\AuthorisationCodeGrantInterface;

use OAuth2\Providers\Pinterest\UserProfile as PinterestUserProfile;

class Pinterest extends OAuth implements UserProfilesInterface, AuthoriseEndpointInterface, TokenEndpointInterface, AuthorisationCodeGrantInterface
{
    use AuthoriseEndpoint;
    use TokenEndpoint;

    use AuthorisationCodeGrant;

    /**
     * Session prefix.
     *
     * @var string
     */
    public $session_prefix = 'pinterest_';

    /**
     * Base API URL.
     *
     * @var string
     */
    public $base_api_endpoint = 'https://api.pinterest.com/v1/';

    /**
     * OAuth 2.0 authorise endpoint.
     *
     * @var string
     */
    public $authorise_endpoint = 'https://api.pinterest.com/oauth/';

    /**
     * OAuth 2.0 token endpoint.
     *
     * @var string
     */
    public $token_endpoint = 'oauth/token';

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
     * @return \OAuth2\Providers\Pinterest\UserProfile
     */
    public function getUserProfile(): UserProfile
    {
        $response = $this->api('GET', 'me/');

        $user = new PinterestUserProfile(isset($response->data->id) ? $response->data->id : '');

        $user->response = $response;
        $user->name = $response->data->first_name . ' ' . $response->data->last_name;
        $user->url = $response->data->url;

        if (preg_match('/https:\/\/www\.pinterest\.com\/([a-zA-Z0-9-_]+)\//', $response->data->url, $match)) {
            $user->username = $match[1];
        }

        return $user;
    }
}
