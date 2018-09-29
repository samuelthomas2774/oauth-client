<?php

namespace OAuth2\Providers\Deezer;

use OAuth2\OAuth;
use OAuth2\AccessToken;
use OAuth2\UserProfile;
use OAuth2\UserProfilesInterface;

use OAuth2\Providers\Deezer\UserProfile as DeezerUserProfile;

use Psr\Http\Message\ResponseInterface;

class Deezer extends OAuth implements UserProfilesInterface
{
    /**
     * Session prefix.
     *
     * @var string
     */
    public $session_prefix = 'deezer_';

    /**
     * Base API URL.
     *
     * @var string
     */
    public $base_api_endpoint = 'https://api.deezer.com';

    /**
     * OAuth 2.0 authorise endpoint.
     *
     * @var string
     */
    public $authorise_endpoint = 'https://connect.deezer.com/oauth/auth.php';

    /**
     * OAuth 2.0 token endpoint.
     *
     * @var string
     */
    public $token_endpoint = 'https://connect.deezer.com/oauth/access_token.php';

    /**
     * Scope separator.
     * This *should* be " " to be compliant with the OAuth 2.0 specification, however
     * some providers use "," instead.
     *
     * @var string
     */
    public $scope_separator = ',';

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

        $options['query']['access_token'] = $token->getAccessToken();

        return $options;
    }

    protected function getApiResponse(string $method, string $url, array $options, ResponseInterface $response)
    {
        if ($url === $this->token_endpoint) {
            parse_str($response->getBody(), $data);
            return (object)$data;
        }

        return json_decode($response->getBody());
    }

    /**
     * Creates an {@see OAuth2\AccessToken} object from a successful response from the token endpoint.
     *
     * @param mixed $response
     * @param array $requested_scope
     * @return \OAuth2\AccessToken
     */
    protected function createAccessTokenFromSuccessfulResponse($response, array $requested_scope = []): AccessToken
    {
        $refresh_token = isset($response->refresh_token) ? $response->refresh_token : null;
        $expires = isset($response->expires) ? time() + $response->expires : null;
        $scope = isset($response->scope) ? explode($this->scope_separator, $response->scope) : $requested_scope;

        $token = new AccessToken($response->access_token, $refresh_token, $expires, $scope);
        $token->response = $response;
        $token->requested_scope = $requested_scope;
        return $token;
    }

    /**
     * Returns the current user.
     *
     * @return \OAuth2\Providers\Deezer\UserProfile
     */
    public function getUserProfile(): UserProfile
    {
        $response = $this->api('GET', 'user/me');

        $user = new DeezerUserProfile(isset($response->id) ? $response->id : '');

        $user->response = $response;
        $user->name = $response->name;
        $user->email_addresses = [$response->email];
        $user->url = $response->link;

        return $user;
    }
}
