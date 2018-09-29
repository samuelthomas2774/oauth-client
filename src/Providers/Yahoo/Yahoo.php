<?php

namespace OAuth2\Providers\Yahoo;

use OAuth2\OAuth;
use OAuth2\UserProfilesInterface;
use OAuth2\UserPicturesInterface;
use OAuth2\UserProfile;

use OAuth2\Providers\Yahoo\UserProfile as YahooUserProfile;

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
     * @return \OAuth2\Providers\Yahoo\UserProfile
     */
    public function getUserProfile(): UserProfile
    {
        $response = $this->api('GET', 'user/me/profile');

        $user = new YahooUserProfile(isset($response->profile->guid) ? $response->profile->guid : '');

        $user->response = $response;
        $user->name = $response->profile->nickname;
        $user->email_addresses = [$response->emails->account];

        return $user;
    }

    /**
     * Returns the URL of the current user's picture.
     *
     * @param integer $size
     * @return string
     */
    public function getUserPictureUrl(int $size = 50): ?string
    {
        $response = $this->api('GET', 'user/me/profile/image/' . $size . 'x' . $size);

        if (!isset($response->image->url) || !isset($response->image->width) || !isset($response->image->height)) return null;

        return $response->image->url;
    }
}
