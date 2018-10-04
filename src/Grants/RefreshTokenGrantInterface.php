<?php

namespace OAuth2\Grants;

use OAuth2\AccessToken;

interface RefreshTokenGrantInterface
{
    /**
     * Exchanges a refresh token for an access token.
     *
     * @param \OAuth2\AccessToken|string $refresh_token
     * @return \OAuth2\AccessToken
     */
    public function getAccessTokenFromRefreshToken($refresh_token): AccessToken;

    /**
     * Refreshes the current access token.
     *
     * @return \OAuth2\AccessToken
     */
    public function refreshAccessToken(): AccessToken;
}
