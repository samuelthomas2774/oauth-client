<?php

namespace OAuth2\Grants;

use OAuth2\AccessToken;
use OAuth2\AuthoriseEndpointInterface;

interface AuthorisationCodeGrantInterface extends AuthoriseEndpointInterface
{
    /**
     * Exchanges a code for an access token.
     *
     * @param string $code
     * @param string $redirect_url
     * @param array $requested_scope The requested scope to use in the {@see OAuth2\AccessToken} object if none is available
     * @param boolean $update_session
     * @return \OAuth2\AccessToken
     */
    public function getAccessTokenFromCode(string $code, string $redirect_url, array $requested_scope = [], bool $update_session = true): AccessToken;

    /**
     * Uses the code in $_GET['code'] to get an access token.
     *
     * @param string $redirect_url
     * @param array $requested_scope The requested scope to use in the {@see OAuth2\AccessToken} object if none is available
     * @param boolean $update_session
     * @return \OAuth2\AccessToken
     */
    public function getAccessTokenFromRequestCode(string $redirect_url, array $requested_scope = [], bool $update_session = true): AccessToken;

    /**
     * Validates $_GET['state'] and uses the code in $_GET['code'] to get an access token.
     * The state's data is used to get the correct redirect_url and requested_scope.
     *
     * @param boolean $update_session
     * @return \OAuth2\AccessToken
     */
    public function getAccessTokenFromRequestCodeAndState(bool $update_session = true): AccessToken;
}
