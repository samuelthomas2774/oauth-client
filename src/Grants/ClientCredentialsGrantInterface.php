<?php

namespace OAuth2\Grants;

use OAuth2\AccessToken;

interface ClientCredentialsGrantInterface
{
    /**
     * Exchanges the client credentials for an access token.
     *
     * @return \OAuth2\AccessToken
     */
    public function getAccessTokenFromClientCredentials(): AccessToken;
}
