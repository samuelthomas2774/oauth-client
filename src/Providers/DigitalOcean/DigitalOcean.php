<?php

namespace OAuth2\Providers\DigitalOcean;

use OAuth2\OAuth;
use OAuth2\AccessToken;
use OAuth2\UserProfile;
use OAuth2\UserProfilesInterface;

use OAuth2\AuthoriseEndpoint;
use OAuth2\TokenEndpoint;

use OAuth2\Grants\AuthorisationCodeGrant;
use OAuth2\Grants\AuthorisationCodeGrantInterface;

use OAuth2\Providers\DigitalOcean\UserProfile as DigitalOceanUserProfile;

class DigitalOcean extends OAuth implements UserProfilesInterface, AuthorisationCodeGrantInterface
{
    use AuthoriseEndpoint;
    use TokenEndpoint;

    use AuthorisationCodeGrant;

    /**
     * Session prefix.
     *
     * @var string
     */
    public $session_prefix = 'digitalocean_';

    /**
     * Base API URL.
     *
     * @var string
     */
    public $base_api_endpoint = 'https://api.digitalocean.com/v2/';

    /**
     * OAuth 2.0 authorise endpoint.
     *
     * @var string
     */
    public $authorise_endpoint = 'https://cloud.digitalocean.com/v1/oauth/authorize';

    /**
     * OAuth 2.0 token endpoint.
     *
     * @var string
     */
    public $token_endpoint = 'https://cloud.digitalocean.com/v1/oauth/token';

    /**
     * Returns the current user.
     *
     * @return \OAuth2\Providers\DigitalOcean\UserProfile
     */
    public function getUserProfile(): UserProfile
    {
        $response = $this->api('GET', 'account');

        $user = new DigitalOceanUserProfile($response->account->uuid);

        $user->response = $response;
        // $user->name = $response->username;
        $user->email_addresses = [$response->account->email];

        return $user;
    }
}
