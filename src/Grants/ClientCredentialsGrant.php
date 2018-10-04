<?php

namespace OAuth2\Grants;

use OAuth2\AccessToken;

use stdClass;
use Throwable;

trait ClientCredentialsGrant
{
    /**
     * Exchanges the client credentials for an access token.
     *
     * @return \OAuth2\AccessToken
     */
    public function getAccessTokenFromClientCredentials(): AccessToken
    {
        $response = $this->api('POST', $this->token_endpoint, [
            'form_params' => [
                'grant_type' => 'client_credentials',
                'client_id' => $this->client_id,
                'client_secret' => $this->client_secret,
            ],
        ], false);

        if (isset($response->access_token)) {
            return $this->createAccessTokenFromSuccessfulResponse($response);
        } else {
            $this->handleErrorFromOAuthTokenResponse($response);
        }
    }

    // \OAuth2\TokenEndpoint
    abstract function createAccessTokenFromSuccessfulResponse(stdClass $response, array $requested_scope = []): AccessToken;
    abstract function handleErrorFromOAuthTokenResponse($response, Throwable $previous = null);
}
