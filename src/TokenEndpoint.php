<?php

namespace OAuth2;

use OAuth2\Exceptions\OAuthException;
use OAuth2\Exceptions\InvalidRequestException;
use OAuth2\Exceptions\InvalidScopeException;
use OAuth2\Exceptions\UnauthorisedClientException;
use OAuth2\Exceptions\InvalidClientException;
use OAuth2\Exceptions\InvalidGrantException;
use OAuth2\Exceptions\UnsupportedGrantTypeException;

use stdClass;
use Throwable;

trait TokenEndpoint
{
    /**
     * Creates an {@see OAuth2\AccessToken} object from a successful response from the token endpoint.
     *
     * @param object $response
     * @param array $requested_scope
     * @return \OAuth2\AccessToken
     */
    protected function createAccessTokenFromSuccessfulResponse(stdClass $response, array $requested_scope = []): AccessToken
    {
        $refresh_token = isset($response->refresh_token) ? $response->refresh_token : null;
        $expires = isset($response->expires_in) ? time() + $response->expires_in : null;
        $scope = !isset($response->scope) ? $requested_scope :
            strlen($response->scope) === 0 ? [] : explode($this->scope_separator, $response->scope);

        $token = new AccessToken($response->access_token, $refresh_token, $expires, $scope);
        $token->response = $response;
        $token->requested_scope = $requested_scope;
        return $token;
    }

    /**
     * Handles an error response from the token endpoint.
     * https://tools.ietf.org/html/rfc6749#section-5.2
     *
     * @param object $response
     * @param \Throwable $previous
     */
    protected function handleErrorFromOAuthTokenResponse($response, Throwable $previous = null)
    {
        switch (is_object($response) && isset($response->error) ? $response->error : $response) {
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
