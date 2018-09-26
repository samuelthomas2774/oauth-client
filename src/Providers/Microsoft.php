<?php

namespace OAuth2\Providers;

use OAuth2\OAuth;
use OAuth2\UserProfilesInterface;

use stdClass;

class Microsoft extends OAuth implements UserProfilesInterface
{
    /**
     * Session prefix.
     *
     * @var string
     */
    public $session_prefix = 'microsoft_';

    /**
     * Base API URL.
     *
     * @var string
     */
    public $base_api_endpoint = 'https://apis.live.net/v5.0/';

    /**
     * OAuth 2.0 authorise endpoint.
     *
     * @var string
     */
    public $authorise_endpoint = 'https://login.live.com/oauth20_authorize.srf';

    /**
     * OAuth 2.0 token endpoint.
     *
     * @var string
     */
    public $token_endpoint = 'https://login.live.com/oauth20_token.srf';

    /**
     * Returns the current user.
     *
     * @return \stdClass
     */
    public function getUserProfile()
    {
        $response = $this->api('GET', 'me');

        $user = new stdClass();
        $user->id = isset($response->id) ? $response->id : null;
        $user->username = is_string($user->id) || is_numeric($user->id) ? (string)$user->id : null;
        $user->name = isset($response->name) ? $response->name : $user->username;
        $user->email = isset($user->response->emails->account) ? $user->response->emails->account : null;
        $user->response = $response;

        return $user;
    }
}
