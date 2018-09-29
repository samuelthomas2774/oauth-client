<?php

namespace OAuth2\Providers\Microsoft;

use OAuth2\OAuth;
use OAuth2\UserProfilesInterface;
use OAuth2\UserProfile;

use OAuth2\Providers\Microsoft\UserProfile as MicrosoftUserProfile;

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
     * @return \OAuth2\Providers\Microsoft\UserProfile
     */
    public function getUserProfile(): UserProfile
    {
        $response = $this->api('GET', 'me');

        $user = new MicrosoftUserProfile(isset($response->id) ? $response->id : '');

        $user->response = $response;
        $user->name = $response->name;

        if (isset($response->emails) && isset($response->emails->account)) $user->email_addresses = [$response->emails->account];

        return $user;
    }
}
