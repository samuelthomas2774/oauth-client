<?php

namespace OAuth2\Providers\WordPress;

use OAuth2\OAuth;
use OAuth2\UserProfile;
use OAuth2\UserProfilesInterface;

use OAuth2\AuthoriseEndpoint;
use OAuth2\TokenEndpoint;
use OAuth2\AuthoriseEndpointInterface;
use OAuth2\TokenEndpointInterface;

use OAuth2\Grants\AuthorisationCodeGrant;
use OAuth2\Grants\AuthorisationCodeGrantInterface;

use OAuth2\Providers\WordPress\UserProfile as WordPressUserProfile;

class WordPress extends OAuth implements UserProfilesInterface, AuthoriseEndpointInterface, TokenEndpointInterface, AuthorisationCodeGrantInterface
{
    use AuthoriseEndpoint;
    use TokenEndpoint;

    use AuthorisationCodeGrant;

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
     * @return \OAuth2\Providers\WordPress\UserProfile
     */
    public function getUserProfile(): UserProfile
    {
        $response = $this->api('GET', 'me');

        $user = new WordPressUserProfile(isset($response->ID) ? $response->ID : '');

        $user->response = $response;
        $user->username = $response->username;
        $user->name = $response->display_name;
        $user->email_addresses = [$response->email];
        $user->url = $response->profile_URL;

        return $user;
    }
}
