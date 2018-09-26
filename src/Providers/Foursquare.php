<?php

namespace OAuth2\Providers;

use OAuth2\OAuth;
use OAuth2\AccessToken;
use OAuth2\UserProfilesInterface;

class Foursquare extends OAuth implements UserProfilesInterface
{
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

    protected function getGuzzleDefaultOptions()
    {
        $options = parent::getGuzzleDefaultOptions();

        if (!isset($options['query'])) $options['query'] = $options['query'] = [];

        $options['query']['v'] = '20140806';
        $options['query']['m'] = 'foursquare';

        return $options;
    }

    /**
     * Returns the request options with an Authorization header with the access token.
     *
     * @param string $method
     * @param string $url
     * @param array $options
     * @param \OAuth2\AccessToken $token
     * @return array $options
     */
    protected function authenticateAccessTokenToApiRequestOptions(string $method, string $url, array $options, AccessToken $token): array
    {
        if (!isset($options['query']) || !is_array($options['query'])) $options['query'] = [];

        $options['query']['oauth_token'] = $token->getAccessToken();

        return $options;
    }

    /**
     * Returns the current user.
     *
     * @return \stdClass
     */
    public function getUserProfile()
    {
        $response = $this->api('GET', 'users/self');

        return $response->response->user;
    }
}
