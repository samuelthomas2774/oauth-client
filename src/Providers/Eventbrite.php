<?php

namespace OAuth2\Providers;

use OAuth2\OAuth;
use OAuth2\UserProfilesInterface;

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
     * @return \stdClass
     */
    public function getUserProfile()
    {
        $response = $this->api('GET', 'users/me/');

        return $response;
    }
}
