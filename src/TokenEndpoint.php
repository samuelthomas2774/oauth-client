<?php

namespace OAuth2;

use OAuth2\Exceptions\OAuthException;
use OAuth2\Exceptions\InvalidRequestException;
use OAuth2\Exceptions\InvalidScopeException;
use OAuth2\Exceptions\UnauthorisedClientException;
use OAuth2\Exceptions\InvalidClientException;
use OAuth2\Exceptions\InvalidGrantException;
use OAuth2\Exceptions\UnsupportedGrantTypeException;

use Throwable;

trait TokenEndpoint
{
    /**
     * Creates an {@see OAuth2\AccessToken} object from a successful response from the token endpoint.
     *
     * @param mixed $response
     * @param array $requested_scope
     * @return \OAuth2\AccessToken
     */
    protected function createAccessTokenFromSuccessfulResponse($response, array $requested_scope = []): AccessToken
    {
        $refresh_token = isset($response->refresh_token) ? $response->refresh_token : null;
        $expires = isset($response->expires_in) ? time() + $response->expires_in : null;
        $scope = isset($response->scope) ? explode($this->scope_separator, $response->scope) : $requested_scope;

        $token = new AccessToken($response->access_token, $refresh_token, $expires, $scope);
        $token->response = $response;
        $token->requested_scope = $requested_scope;
        return $token;
    }

    // https://tools.ietf.org/html/rfc6749#section-5.2
    protected function handleErrorFromOAuthTokenResponse($response, Throwable $previous = null)
    {
        switch ($response->error) {
            default:
                throw OAuthException::fromResponse($response, $previous);
            case 'invalid_request':
                throw InvalidRequestException::fromResponse($response, $previous);
            case 'invalid_client':
                throw InvalidClientException::fromResponse($response, $previous);
            case 'invalid_grant':
                throw InvalidGrantException::fromResponse($response, $previous);
            case 'unauthorized_client':
                throw UnauthorisedClientException::fromResponse($response, $previous);
            case 'unsupported_grant_type':
                throw UnsupportedGrantTypeException::fromResponse($response, $previous);
            case 'invalid_scope':
                throw InvalidScopeException::fromResponse($response, $previous);
        }
    }
}
