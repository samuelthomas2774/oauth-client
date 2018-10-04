<?php

namespace OAuth2\Providers\Foursquare;

use OAuth2\OAuth;
use OAuth2\AccessToken;
use OAuth2\UserProfile;
use OAuth2\UserProfilesInterface;

use OAuth2\AuthoriseEndpoint;
use OAuth2\TokenEndpoint;
use OAuth2\AuthoriseEndpointInterface;

use OAuth2\Grants\AuthorisationCodeGrant;
use OAuth2\Grants\AuthorisationCodeGrantInterface;

use OAuth2\UsesAccessTokenQueryParameter;

use OAuth2\Providers\Foursquare\UserProfile as FoursquareUserProfile;

class Foursquare extends OAuth implements UserProfilesInterface, AuthoriseEndpointInterface, AuthorisationCodeGrantInterface
{
    use AuthoriseEndpoint;
    use TokenEndpoint;

    use AuthorisationCodeGrant;

    use UsesAccessTokenQueryParameter;

    /**
     * Session prefix.
     *
     * @var string
     */
    public $session_prefix = 'foursquare_';

    /**
     * Base API URL.
     *
     * @var string
     */
    public $base_api_endpoint = 'https://api.foursquare.com/v2/';

    /**
     * OAuth 2.0 authorise endpoint.
     *
     * @var string
     */
    public $authorise_endpoint = 'https://foursquare.com/oauth2/authorize';

    /**
     * OAuth 2.0 token endpoint.
     *
     * @var string
     */
    public $token_endpoint = 'https://foursquare.com/oauth2/access_token';

    public $access_token_parameter_name = 'oauth_token';

    protected function getGuzzleOptionsForRequest(string $method, string $url, array $options = []): array
    {
        $options = parent::getGuzzleOptionsForRequest($method, $url, $options);

        if (!isset($options['query'])) $options['query'] = [];

        $options['query']['v'] = '20180929';
        $options['query']['m'] = 'foursquare';

        return $options;
    }

    /**
     * Returns the current user.
     *
     * @return \OAuth2\Providers\Foursquare\UserProfile
     */
    public function getUserProfile(): UserProfile
    {
        $response = $this->api('GET', 'users/self');

        $user = new FoursquareUserProfile(isset($response->response->user->id) ? $response->response->user->id : '');

        $user->response = $response;
        $user->name = $response->response->user->firstName . ' ' . $response->response->user->lastName;
        $user->email_addresses = [$response->response->user->contact->email];
        $user->url = $response->response->user->canonicalUrl;

        return $user;
    }
}
