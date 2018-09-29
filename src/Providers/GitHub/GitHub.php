<?php

namespace OAuth2\Providers\GitHub;

use OAuth2\OAuth;
use OAuth2\AccessToken;
use OAuth2\UserProfile;
use OAuth2\UserProfilesInterface;
use OAuth2\UserPicturesInterface;

use OAuth2\Providers\GitHub\UserProfile as GitHubUserProfile;

use Psr\Http\Message\ResponseInterface;

class GitHub extends OAuth implements UserProfilesInterface, UserPicturesInterface
{
    /**
     * Session prefix.
     *
     * @var string
     */
    public $session_prefix = 'github_';

    /**
     * Base API URL.
     *
     * @var string
     */
    public $base_api_endpoint = 'https://api.github.com';

    /**
     * OAuth 2.0 authorise endpoint.
     *
     * @var string
     */
    public $authorise_endpoint = 'https://github.com/login/oauth/authorize';

    /**
     * OAuth 2.0 token endpoint.
     *
     * @var string
     */
    public $token_endpoint = 'https://github.com/login/oauth/access_token';

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
        if (!isset($options['headers']) || !is_array($options['headers'])) $options['headers'] = [];

        $options['headers']['Authorization'] = 'token ' . $token->getAccessToken();

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
     * Returns the current user.
     *
     * @return \OAuth2\Providers\GitHub\UserProfile
     */
    public function getUserProfile(): UserProfile
    {
        $response = $this->api('GET', 'user');

        $user = new GitHubUserProfile(isset($response->id) ? $response->id : '');

        $user->response = $response;
        $user->name = $response->name;
        $user->url = $response->html_url;

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
        $response = $this->api('GET', 'user');

        if (!isset($response->avatar_url)) return null;

        return $response->avatar_url;
    }
}
