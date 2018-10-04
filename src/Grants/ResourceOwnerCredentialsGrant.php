<?php

namespace OAuth2\Grants;

use OAuth2\AccessToken;

use Throwable;

trait ResourceOwnerCredentialsGrant
{
    /**
     * Exchanges a username and password for an access token.
     *
     * @param string $username
     * @param string $password
     * @return \OAuth2\AccessToken
     */
    public function getAccessTokenFromUserCredentials(string $username, string $password): AccessToken
    {
        $response = $this->api('POST', $this->token_endpoint, [
            'form_params' => [
                'grant_type' => 'password',
                'client_id' => $this->client_id,
                'client_secret' => $this->client_secret,
                'username' => $username,
                'password' => $password,
            ]
        ], false);

        if (isset($response->access_token)) {
            return $this->createAccessTokenFromSuccessfulResponse($response);
        } else {
            $this->handleErrorFromOAuthTokenResponse($response);
        }
    }

    // \OAuth2\TokenEndpoint
    abstract function createAccessTokenFromSuccessfulResponse($response, array $requested_scope = []): AccessToken;
    abstract function handleErrorFromOAuthTokenResponse($response, Throwable $previous = null);
}
