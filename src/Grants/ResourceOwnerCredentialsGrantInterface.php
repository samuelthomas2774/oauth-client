<?php

namespace OAuth2\Grants;

use OAuth2\AccessToken;

interface ResourceOwnerCredentialsGrantInterface
{
    /**
     * Exchanges a username and password for an access token.
     *
     * @param string $username
     * @param string $password
     * @return \OAuth2\AccessToken
     */
    public function getAccessTokenFromUserCredentials(string $username, string $password): AccessToken;
}
