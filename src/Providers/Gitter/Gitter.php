<?php

namespace OAuth2\Providers\Gitter;

use OAuth2\OAuth;
use OAuth2\AccessToken;
use OAuth2\UserProfile;
use OAuth2\UserProfilesInterface;

use OAuth2\Providers\Gitter\UserProfile as GitterUserProfile;

class Gitter extends OAuth implements UserProfilesInterface
{
    /**
     * Session prefix.
     *
     * @var string
     */
    public $session_prefix = 'gitter_';

    /**
     * Base API URL.
     *
     * @var string
     */
    public $base_api_endpoint = 'https://api.gitter.im/v1/';

    /**
     * OAuth 2.0 authorise endpoint.
     *
     * @var string
     */
    public $authorise_endpoint = 'https://gitter.im/login/oauth/authorize';

    /**
     * OAuth 2.0 token endpoint.
     *
     * @var string
     */
    public $token_endpoint = 'https://gitter.im/login/oauth/token';

    /**
     * Returns the current user.
     *
     * @return \OAuth2\Providers\Gitter\UserProfile
     */
    public function getUserProfile(): UserProfile
    {
        $response = $this->api('GET', 'user/me');

        $user = new GitterUserProfile($response->id);

        $user->response = $response;
        $user->username = $response->username;
        $user->name = $response->displayName;
        $user->url = 'https://gitter.im' . $response->url;

        return $user;
    }
}
