<?php

namespace OAuth2\Providers\Amazon;

use OAuth2\OAuth;
use OAuth2\UserProfile;

use OAuth2\Providers\Amazon\UserProfile as AmazonUserProfile;

class Amazon extends OAuth
{
    /**
     * Session prefix.
     *
     * @var string
     */
    public $session_prefix = 'amazon_';

    /**
     * Base API URL.
     *
     * @var string
     */
    public $base_api_endpoint = 'https://api.amazon.com';

    /**
     * OAuth 2.0 authorise endpoint.
     *
     * @var string
     */
    public $authorise_endpoint = 'https://www.amazon.com/ap/oa';

    /**
     * OAuth 2.0 token endpoint.
     *
     * @var string
     */
    public $token_endpoint = '/auth/o2/token';

    /**
     * Returns the current user.
     *
     * @return \OAuth2\Providers\Amazon\UserProfile
     */
    public function getUserProfile(): UserProfile
    {
        $response = $this->api('GET', 'user/profile');

        $user = new AmazonUserProfile(isset($response->user_id) ? $response->user_id : '');

        $user->response = $response;
        $user->name = $response->name;
        $user->email_addresses = [$response->email];

        return $user;
    }
}
