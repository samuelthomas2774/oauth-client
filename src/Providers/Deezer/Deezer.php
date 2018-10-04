<?php

namespace OAuth2\Providers\Deezer;

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

use OAuth2\Providers\Deezer\UserProfile as DeezerUserProfile;

use Psr\Http\Message\ResponseInterface;

class Deezer extends OAuth implements UserProfilesInterface, AuthoriseEndpointInterface, AuthorisationCodeGrantInterface
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
