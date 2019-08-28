<?php

namespace OAuth2\Providers\Instagram;

use OAuth2\OAuth;
use OAuth2\AccessToken;
use OAuth2\UserProfile;
use OAuth2\UserProfilesInterface;

use OAuth2\AuthoriseEndpoint;
use OAuth2\TokenEndpoint;

use OAuth2\Grants\AuthorisationCodeGrant;
use OAuth2\Grants\AuthorisationCodeGrantInterface;

use OAuth2\UsesAccessTokenQueryParameter;

use OAuth2\Providers\Instagram\UserProfile as InstagramUserProfile;

class Instagram extends OAuth implements UserProfilesInterface, AuthorisationCodeGrantInterface
{
    use AuthoriseEndpoint;
    use TokenEndpoint;

    use AuthorisationCodeGrant;

    use UsesAccessTokenQueryParameter;

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
     * Returns the current user.
     *
     * @return \OAuth2\Providers\Instagram\UserProfile
     */
    public function getUserProfile(): UserProfile
    {
        $response = $this->api('GET', 'users/self/');

        $user = new InstagramUserProfile(isset($response->data->id) ? $response->data->id : '');

        $user->response = $response;
        $user->username = $response->data->username;
        $user->name = $response->data->full_name;

        return $user;
    }
}
