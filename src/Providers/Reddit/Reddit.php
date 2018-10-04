<?php

namespace OAuth2\Providers\Reddit;

use OAuth2\OAuth;
use OAuth2\AccessToken;
use OAuth2\UserProfile;
use OAuth2\UserProfilesInterface;

use OAuth2\AuthoriseEndpoint;
use OAuth2\TokenEndpoint;
use OAuth2\AuthoriseEndpointInterface;
use OAuth2\TokenEndpointInterface;

use OAuth2\Grants\AuthorisationCodeGrant;
use OAuth2\Grants\AuthorisationCodeGrantInterface;

use OAuth2\Providers\Reddit\UserProfile as RedditUserProfile;

use TypeError;

class Reddit extends OAuth implements UserProfilesInterface, AuthoriseEndpointInterface, TokenEndpointInterface, AuthorisationCodeGrantInterface
{
    use AuthoriseEndpoint;
    use TokenEndpoint;

    use AuthorisationCodeGrant;

    /**
     * Session prefix.
     *
     * @var string
     */
    public $session_prefix = 'reddit_';

    /**
     * Base API URL.
     *
     * @var string
     */
    public $base_api_endpoint = 'https://oauth.reddit.com/api/v1/';

    /**
     * OAuth 2.0 authorise endpoint.
     *
     * @var string
     */
    public $authorise_endpoint = 'https://www.reddit.com/api/v1/authorize';

    /**
     * OAuth 2.0 token endpoint.
     *
     * @var string
     */
    public $token_endpoint = 'https://www.reddit.com/api/v1/access_token';

    /**
     * Exchanges a code for an access token.
     *
     * @param string $code
     * @param string $redirect_url
     * @param array $requested_scope The requested scope to use in the {@see OAuth2\AccessToken} object if none is available
     * @param boolean $update_session
     * @return \OAuth2\AccessToken
     */
    public function getAccessTokenFromCode(string $code, string $redirect_url, array $requested_scope = [], bool $update_session = true): AccessToken
    {
        // Check if redirect_url is a url - the redirect_url should be exactly the same as the redirect_url used in the login dialog (so really, this should just be the same as the current url)
        if (!filter_var($redirect_url, FILTER_VALIDATE_URL)) {
            throw new TypeError('$redirect_url must be a valid URL.');
        }

        $response = $this->api('POST', $this->token_endpoint, [
            'form_params' => [
                'grant_type' => 'authorization_code',
                'redirect_uri' => $redirect_url,
                'code' => $code,
            ],
        ], true);

        if (isset($response->access_token)) {
            $token = $this->createAccessTokenFromSuccessfulResponse($response, $requested_scope);

            if ($update_session) $this->setAccessToken($token);

            return $token;
        } else {
            $this->handleErrorFromOAuthTokenResponse($response);
        }
    }

    /**
     * Returns the current user.
     *
     * @return \OAuth2\Providers\Reddit\UserProfile
     */
    public function getUserProfile(): UserProfile
    {
        $response = $this->api('GET', 'me');

        $user = new RedditUserProfile($response->id);

        $user->response = $response;
        $user->username = $response->name;
        $user->name = $response->subreddit->title;
        $user->url = 'https://www.reddit.com' . $response->subreddit->url;

        return $user;
    }
}
