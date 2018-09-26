<?php

namespace OAuth2\Providers;

use OAuth2\OAuth;
use OAuth2\UserProfilesInterface;
use OAuth2\UserPicturesInterface;

use stdClass;

class Yahoo extends OAuth implements UserProfilesInterface, UserPicturesInterface
{
    /**
     * Session prefix.
     *
     * @var string
     */
    public $session_prefix = 'yahoo_';

    /**
     * Base API URL.
     *
     * @var string
     */
    public $base_api_endpoint = 'https://social.yahooapis.com/v1/';

    /**
     * OAuth 2.0 authorise endpoint.
     *
     * @var string
     */
    public $authorise_endpoint = 'https://api.login.yahoo.com/oauth2/request_auth';

    /**
     * OAuth 2.0 token endpoint.
     *
     * @var string
     */
    public $token_endpoint = 'https://api.login.yahoo.com/oauth2/get_token';

    public function __construct(string $client_id, string $client_secret, $token = null, array $options = [])
    {
        $this->api_headers['Accept'] = 'application/json';

        parent::__construct($client_id, $client_secret, $token, $options);
    }

    /**
     * Returns the current user.
     *
     * @return \stdClass
     */
    public function getUserProfile()
    {
        $response = $this->api('GET', 'user/me/profile');

        $user = new stdClass();
        $user->id = isset($response->profile->guid) ? $response->profile->guid : null;
        $user->username = is_string($user->id) || is_numeric($user->id) ? $user->id : null;
        $user->name = isset($response->profile->nickname) ? $response->profile->nickname : $user->username;
        $user->email = isset($user->response->emails->account) ? $user->response->emails->account : null;
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
        $response = $this->api('GET', 'user/me/profile/image/' . $size . 'x' . $size);

        if (!isset($response->image->url) || !isset($response->image->width) || !isset($response->image->height)) return;

        return $response->image->url;
    }
}
