<?php

namespace OAuth2\Grants;

use OAuth2\AccessToken;

use Throwable;
use Exception;
use TypeError;

trait AuthorisationCodeGrant
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
    public function getAccessTokenFromCode(string $code, string $redirect_url, array $requested_scope = [], bool $update_session = true): AccessToken
    {
        // Check if redirect_url is a url - the redirect_url should be exactly the same as the redirect_url used in the login dialog (so really, this should just be the same as the current url)
        if (!filter_var($redirect_url, FILTER_VALIDATE_URL)) {
            throw new TypeError('$redirect_url must be a valid URL.');
        }

        $response = $this->api('POST', $this->token_endpoint, [
            'form_params' => [
                'grant_type' => 'authorization_code',
                'client_id' => $this->client_id,
                'client_secret' => $this->client_secret,
                'redirect_uri' => $redirect_url,
                'code' => $code,
            ],
        ], false);

        if (isset($response->access_token)) {
            $token = $this->createAccessTokenFromSuccessfulResponse($response, $requested_scope);

            if ($update_session) $this->setAccessToken($token);

            return $token;
        } else {
            $this->handleErrorFromOAuthTokenResponse($response);
        }
    }

    public function getAccessTokenFromRequestCode(string $redirect_url, array $requested_scope = [], bool $update_session = true)
    {
        if (isset($_GET['error'])) {
            $this->handleErrorFromOAuthAuthoriseRequest($_GET);
        }

        if (!isset($_GET['code']) || !isset($_GET['state'])) {
            throw new Exception('Missing code and state.');
        }

        $state = $this->session('state');
        if (empty($state) || $state !== $_GET['state']) {
            throw new Exception('Invalid state.');
        }

        return $this->getAccessTokenFromCode($_GET['code'], $redirect_url, $requested_scope, $update_session);
    }

    // \OAuth2\AuthoriseEndpoint
    abstract function handleErrorFromOAuthAuthoriseRequest(array $request, Throwable $previous = null);

    // \OAuth2\TokenEndpoint
    abstract function createAccessTokenFromSuccessfulResponse($response, array $requested_scope = []): AccessToken;
    abstract function handleErrorFromOAuthTokenResponse($response, Throwable $previous = null);
}
