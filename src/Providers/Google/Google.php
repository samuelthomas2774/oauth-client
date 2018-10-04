<?php

namespace OAuth2\Providers\Google;

use OAuth2\OAuth;
use OAuth2\UserProfile;
use OAuth2\UserProfilesInterface;
use OAuth2\UserPicturesInterface;

use OAuth2\AuthoriseEndpoint;
use OAuth2\TokenEndpoint;
use OAuth2\AuthoriseEndpointInterface;

use OAuth2\Grants\AuthorisationCodeGrant;
use OAuth2\Grants\AuthorisationCodeGrantInterface;

use OAuth2\Providers\Google\UserProfile as GoogleUserProfile;

class Google extends OAuth implements UserProfilesInterface, UserPicturesInterface, AuthoriseEndpointInterface, AuthorisationCodeGrantInterface
{
    use AuthoriseEndpoint;
    use TokenEndpoint;

    use AuthorisationCodeGrant;

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
     * @return \OAuth2\Providers\Google\UserProfile
     */
    public function getUserProfile(): UserProfile
    {
        $response = $this->api('GET', '/oauth2/v2/userinfo');

        $user = new GoogleUserProfile(isset($response->id) ? $response->id : '');

        $user->response = $response;
        $user->name = $response->name;
        $user->url = $response->link;

        if (isset($response->email)) $user->email_addresses = [$response->email];

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
        $response = $this->api('GET', '/oauth2/v2/userinfo');

        if (!isset($response->picture)) return null;

        return $response->picture . ($size ? '?sz=' . $size : '');
    }
}
