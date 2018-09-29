<?php

namespace OAuth2\Providers\DeviantArt;

use OAuth2\OAuth;
use OAuth2\AccessToken;
use OAuth2\UserProfile;
use OAuth2\UserProfilesInterface;

use OAuth2\Providers\DeviantArt\UserProfile as DeviantArtUserProfile;

class DeviantArt extends OAuth implements UserProfilesInterface
{
    /**
     * Session prefix.
     *
     * @var string
     */
    public $session_prefix = 'deviantart_';

    /**
     * Base API URL.
     *
     * @var string
     */
    public $base_api_endpoint = 'https://www.deviantart.com/api/v1/oauth2/';

    /**
     * OAuth 2.0 authorise endpoint.
     *
     * @var string
     */
    public $authorise_endpoint = 'https://www.deviantart.com/oauth2/authorize';

    /**
     * OAuth 2.0 token endpoint.
     *
     * @var string
     */
    public $token_endpoint = '/oauth2/token';

    /**
     * Returns the current user.
     *
     * @return \OAuth2\Providers\DeviantArt\UserProfile
     */
    public function getUserProfile(): UserProfile
    {
        $response = $this->api('GET', 'user/whoami');

        $user = new DeviantArtUserProfile($response->userid);

        $user->response = $response;
        $user->username = $response->username;

        return $user;
    }
}
