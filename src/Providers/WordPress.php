<?php

namespace OAuth2\Providers;

use OAuth2\OAuth;
use OAuth2\UserProfilesInterface;

class WordPress extends OAuth implements UserProfilesInterface
{
    /**
     * Session prefix.
     *
     * @var string
     */
    public $session_prefix = 'wordpress_';

    /**
     * Base API URL.
     *
     * @var string
     */
    public $base_api_endpoint = 'https://public-api.wordpress.com/rest/v1/';

    /**
     * OAuth 2.0 authorise endpoint.
     *
     * @var string
     */
    public $authorise_endpoint = 'https://public-api.wordpress.com/oauth2/authorize';

    /**
     * OAuth 2.0 token endpoint.
     *
     * @var string
     */
    public $token_endpoint = '/oauth2/token';

    /**
     * Returns the current user.
     *
     * @return \stdClass
     */
    public function getUserProfile()
    {
        $response = $this->api('GET', 'me');

        $response->id = $response->ID;

        return $response;
    }
}
