<?php

namespace OAuth2\Providers\Eventbrite;

use OAuth2\OAuth;
use OAuth2\UserProfilesInterface;
use OAuth2\UserProfile;

use OAuth2\Providers\Eventbrite\UserProfile as EventbriteUserProfile;

class Eventbrite extends OAuth implements UserProfilesInterface
{
    /**
     * Session prefix.
     *
     * @var string
     */
    public $session_prefix = 'eventbrite_';

    /**
     * Base API URL.
     *
     * @var string
     */
    public $base_api_endpoint = 'https://www.eventbriteapi.com/v3/';

    /**
     * OAuth 2.0 authorise endpoint.
     *
     * @var string
     */
    public $authorise_endpoint = 'https://www.eventbrite.com/oauth/authorize';

    /**
     * OAuth 2.0 token endpoint.
     *
     * @var string
     */
    public $token_endpoint = 'https://www.eventbrite.com/oauth/token';

    /**
     * Returns the current user.
     *
     * @return \OAuth2\Providers\Eventbrite\UserProfile
     */
    public function getUserProfile(): UserProfile
    {
        $response = $this->api('GET', 'users/me/');

        $user = new EventbriteUserProfile(isset($response->id) ? $response->id : '');

        $user->response = $response;
        $user->name = $response->name;

        $user->email_addresses = array_map(function ($email_address) {
            return $email_address->email;
        }, $response->emails);

        return $user;
    }
}
