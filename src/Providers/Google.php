<?php

namespace OAuth2\Providers;

use OAuth2\OAuth;
use stdClass;

class Google extends OAuth
{
    /**
     * Session prefix.
     *
     * @var string
     */
    public $session_prefix = 'google_';

    /**
     * Base API URL.
     *
     * @var string
     */
    public $base_api_endpoint = 'https://www.googleapis.com/plus/v1/';

    /**
     * OAuth 2.0 authorise endpoint.
     *
     * @var string
     */
    public $authorise_endpoint = 'https://accounts.google.com/o/oauth2/auth';

    /**
     * OAuth 2.0 token endpoint.
     *
     * @var string
     */
    public $token_endpoint = 'https://accounts.google.com/o/oauth2/token';

    /**
     * Returns the current user.
     *
     * @return \stdClass
     */
    public function getUserProfile()
    {
        $response = $this->api('GET', 'https://www.googleapis.com/oauth2/v2/userinfo');

        $user = new stdClass();
        $user->id = isset($response->id) ? $response->id : null;
        $user->username = is_string($user->id) || is_numeric($user->id) ? (string)$user->id : null;
        $user->name = isset($response->name) ? $response->name : $user->username;
        $user->email = isset($response->email) ? $response->email : null;
        $user->response = $response;

        return $user;
    }

    /**
     * Returns the URL of the current user's picture.
     *
     * @param integer $size
     * @return string
     */
    public function getUserPictureUrl(int $size = 50): string
    {
        $response = $this->api('GET', 'https://www.googleapis.com/oauth2/v2/userinfo');

        if (!isset($response->picture)) return;

        return $response->picture . ($size ? '?sz=' . $size : '');
    }
}
