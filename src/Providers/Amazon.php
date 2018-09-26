<?php

namespace OAuth2\Providers;

use OAuth2\OAuth;

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
     * @return \stdClass
     */
    public function getUserProfile()
    {
        $response = $this->api('GET', 'user/profile');

        $response->id = $response->user_id;

        return $response;
    }
}
