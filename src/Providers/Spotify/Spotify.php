<?php

namespace OAuth2\Providers\Spotify;

use OAuth2\OAuth;
use OAuth2\UserProfile;
use OAuth2\UserProfilesInterface;

use OAuth2\AuthoriseEndpoint;
use OAuth2\TokenEndpoint;
use OAuth2\AuthoriseEndpointInterface;
use OAuth2\TokenEndpointInterface;

use OAuth2\Grants\AuthorisationCodeGrant;
use OAuth2\Grants\AuthorisationCodeGrantInterface;

use OAuth2\Providers\Spotify\UserProfile as SpotifyUserProfile;

class Spotify extends OAuth implements UserProfilesInterface, AuthoriseEndpointInterface, TokenEndpointInterface, AuthorisationCodeGrantInterface
{
    use AuthoriseEndpoint;
    use TokenEndpoint;

    use AuthorisationCodeGrant;

    /**
     * Session prefix.
     *
     * @var string
     */
    public $session_prefix = 'spotify_';

    /**
     * Base API URL.
     *
     * @var string
     */
    public $base_api_endpoint = 'https://api.spotify.com/v1/';

    /**
     * OAuth 2.0 authorise endpoint.
     *
     * @var string
     */
    public $authorise_endpoint = 'https://accounts.spotify.com/authorize';

    /**
     * OAuth 2.0 token endpoint.
     *
     * @var string
     */
    public $token_endpoint = 'https://accounts.spotify.com/api/token';

    /**
     * Returns the current user.
     *
     * @return \OAuth2\Providers\Spotify\UserProfile
     */
    public function getUserProfile(): UserProfile
    {
        $response = $this->api('GET', 'me');

        $user = new SpotifyUserProfile(isset($response->id) ? $response->id : '');

        $user->response = $response;
        $user->username = $response->id;
        $user->name = $response->display_name;
        $user->url = $response->external_urls->spotify;

        return $user;
    }
}
