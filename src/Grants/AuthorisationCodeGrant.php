<?php

namespace OAuth2\Grants;

use OAuth2\AccessToken;
use OAuth2\State;

use stdClass;
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

    /**
     * Uses the code in $_GET['code'] to get an access token.
     *
     * @param string $redirect_url
     * @param array $requested_scope The requested scope to use in the {@see OAuth2\AccessToken} object if none is available
     * @param boolean $update_session
     * @return \OAuth2\AccessToken
     */
    public function getAccessTokenFromRequestCode(string $redirect_url, array $requested_scope = [], bool $update_session = true): AccessToken
    {
        if (isset($_GET['error'])) {
            $this->handleErrorFromOAuthAuthoriseRequest($_GET);
        }

        if (!isset($_GET['code'])) {
            throw new Exception('Missing code.');
        }

        $requested_scope = isset($state->requested_scope) ? $state->requested_scope : [];

        return $this->getAccessTokenFromCode($_GET['code'], $redirect_url, $requested_scope, $update_session);
    }

    /**
     * Validates $_GET['state'] and uses the code in $_GET['code'] to get an access token.
     * The state's data is used to get the correct redirect_url and requested_scope.
     *
     * @param boolean $update_session
     * @return \OAuth2\AccessToken
     */
    public function getAccessTokenFromRequestCodeAndState(bool $update_session = true): AccessToken
    {
        if (isset($_GET['error'])) {
            $this->handleErrorFromOAuthAuthoriseRequest($_GET);
        }

        if (!$state = $this->getRequestState()) {
            throw new Exception('Invalid state.');
        }

        return $this->getAccessTokenFromCode($_GET['code'], $state->redirect_url, $state->requested_scope, $update_session);
    }

    /**
     * Returns the state matching the request query string parameter.
     *
     * @return \OAuth2\State
     */
    public function getRequestState(): ?State
    {
        if (!isset($_GET['state'])) return null;

        return $this->getLastStateById($_GET['state']);
    }

    // \OAuth2\AuthoriseEndpoint
    abstract protected function handleErrorFromOAuthAuthoriseRequest(array $request, Throwable $previous = null);

    // \OAuth2\TokenEndpoint
    abstract protected function createAccessTokenFromSuccessfulResponse(stdClass $response, array $requested_scope = []): AccessToken;
    abstract protected function handleErrorFromOAuthTokenResponse($response, Throwable $previous = null);
}
