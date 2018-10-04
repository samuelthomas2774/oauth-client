<?php

namespace OAuth2\Grants;

use OAuth2\AccessToken;

use Throwable;
use TypeError;

trait RefreshTokenGrant
{
    /**
     * Exchanges a refresh token for an access token.
     *
     * @param \OAuth2\AccessToken|string $refresh_token
     * @return \OAuth2\AccessToken
     */
    public function getAccessTokenFromRefreshToken($refresh_token): AccessToken
    {
        if ($refresh_token instanceof AccessToken) $refresh_token = $refresh_token->getRefreshToken();
        if (!is_string($refresh_token)) throw new TypeError('$refresh_token must be an OAuth2\AccessToken object with a refresh token or a string.');

        $response = $this->api('POST', $this->token_endpoint, [
            'form_params' => [
                'grant_type' => 'refresh_token',
                'client_id' => $this->client_id,
                'client_secret' => $this->client_secret,
                'refresh_token' => $refresh_token,
            ]
        ], false);

        if (isset($response->access_token)) {
            return $this->createAccessTokenFromSuccessfulResponse($response);
        } else {
            $this->handleErrorFromOAuthTokenResponse($response);
        }
    }

    /**
     * Refreshes the current access token.
     *
     * @return \OAuth2\AccessToken
     */
    public function refreshAccessToken(): AccessToken
    {
        return $this->getAccessTokenFromRefreshToken($this->getAccessToken());
    }

    // \OAuth2\TokenEndpoint
    abstract function createAccessTokenFromSuccessfulResponse($response, array $requested_scope = []): AccessToken;
    abstract function handleErrorFromOAuthTokenResponse($response, Throwable $previous = null);
}
