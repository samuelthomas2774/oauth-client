<?php

namespace OAuth2\Providers\SpeechMore;

use OAuth2\OAuth;
use OAuth2\UserProfile;
use OAuth2\UserProfilesInterface;
use OAuth2\UserPicturesInterface;

use OAuth2\AuthoriseEndpoint;
use OAuth2\TokenEndpoint;
use OAuth2\AuthoriseEndpointInterface;
use OAuth2\TokenEndpointInterface;

use OAuth2\Grants\AuthorisationCodeGrant;
use OAuth2\Grants\RefreshTokenGrant;
use OAuth2\Grants\ImplicitGrant;
use OAuth2\Grants\ResourceOwnerCredentialsGrant;
use OAuth2\Grants\ClientCredentialsGrant;

use OAuth2\Grants\AuthorisationCodeGrantInterface;
use OAuth2\Grants\RefreshTokenGrantInterface;
use OAuth2\Grants\ImplicitGrantInterface;
use OAuth2\Grants\ResourceOwnerCredentialsGrantInterface;
use OAuth2\Grants\ClientCredentialsGrantInterface;

use OAuth2\Providers\SpeechMore\UserProfile as SpeechMoreUserProfile;

class SpeechMore extends OAuth implements UserProfilesInterface, UserPicturesInterface, AuthoriseEndpointInterface, TokenEndpointInterface, AuthorisationCodeGrantInterface, RefreshTokenGrantInterface, ImplicitGrantInterface, ResourceOwnerCredentialsGrantInterface, ClientCredentialsGrantInterface
{
    use AuthoriseEndpoint;
    use TokenEndpoint;

    use AuthorisationCodeGrant;
    use RefreshTokenGrant;
    use ImplicitGrant;
    use ResourceOwnerCredentialsGrant;
    use ClientCredentialsGrant;

    /**
     * Session prefix.
     *
     * @var string
     */
    public $session_prefix = 'speechmore_';

    /**
     * Base API URL.
     *
     * @var string
     */
    public $base_api_endpoint = 'https://speechmore.ml/api/';

    /**
     * OAuth 2.0 authorise endpoint.
     *
     * @var string
     */
    public $authorise_endpoint = 'https://speechmore.ml/oauth/authorize';

    /**
     * OAuth 2.0 token endpoint.
     *
     * @var string
     */
    public $token_endpoint = '/oauth/token';

    /**
     * Returns information about the current access token.
     *
     * @return \stdClass
     */
    public function getTokenInfo()
    {
        return $this->api('GET', 'token');
    }

    /**
     * Returns the current user.
     *
     * @return \OAuth2\Providers\SpeechMore\UserProfile
     */
    public function getUserProfile(): UserProfile
    {
        $response = $this->api('GET', 'user');

        $user = new SpeechMoreUserProfile(isset($response->client_relationship_id) ? $response->client_relationship_id : '');

        $user->response = $response;
        $user->name = $response->name;

        if (isset($response->id)) $user->ids = [$response->id];
        if (isset($response->username)) $user->username = $response->username;
        if (isset($response->email)) $user->email_addresses = [$response->email];

        return $user;
    }

    /**
     * Returns the URL of the current user's picture.
     *
     * @param integer $size
     * @return string
     */
    public function getUserPictureUrl(int $size = null): ?string
    {
        $response = $this->api('GET', 'user');

        if (!isset($response->avatar_url)) return null;

        return $response->avatar_url;
    }
}
