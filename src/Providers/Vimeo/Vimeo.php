<?php

namespace OAuth2\Providers\Vimeo;

use OAuth2\OAuth;
use OAuth2\AccessToken;
use OAuth2\UserProfile;
use OAuth2\UserProfilesInterface;

use OAuth2\Providers\Vimeo\UserProfile as VimeoUserProfile;

class Vimeo extends OAuth implements UserProfilesInterface
{
    /**
     * Session prefix.
     *
     * @var string
     */
    public $session_prefix = 'vimeo_';

    /**
     * Base API URL.
     *
     * @var string
     */
    public $base_api_endpoint = 'https://api.vimeo.com';

    /**
     * OAuth 2.0 authorise endpoint.
     *
     * @var string
     */
    public $authorise_endpoint = 'https://api.vimeo.com/oauth/authorize';

    /**
     * OAuth 2.0 token endpoint.
     *
     * @var string
     */
    public $token_endpoint = '/oauth/access_token';

    /**
     * Returns the current user.
     *
     * @return \OAuth2\Providers\Vimeo\UserProfile
     */
    public function getUserProfile(): UserProfile
    {
        $response = $this->api('GET', 'me');

        $user = new VimeoUserProfile(preg_match('/\/users\/([0-9]+)/', $response->uri, $match) ? $match[1] : '');

        $user->response = $response;
        $user->name = $response->name;
        $user->url = $response->link;

        if (preg_match('/https:\/\/vimeo\.com\/([a-zA-Z0-9-_]+)/', $response->link, $match)) {
            $user->username = $match[1];
        }

        return $user;
    }
}
