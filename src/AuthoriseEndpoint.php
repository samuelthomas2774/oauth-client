<?php

namespace OAuth2;

use OAuth2\Exceptions\OAuthException;
use OAuth2\Exceptions\AccessDeniedException;
use OAuth2\Exceptions\InvalidRequestException;
use OAuth2\Exceptions\InvalidScopeException;
use OAuth2\Exceptions\ServerErrorException;
use OAuth2\Exceptions\TemporarilyUnavailableException;
use OAuth2\Exceptions\UnauthorisedClientException;
use OAuth2\Exceptions\UnsupportedResponseTypeException;

use Throwable;
use Exception;

trait AuthoriseEndpoint
{
    use ManagesState;

    /**
     * Generate a URL to redirect users to to authorise this client.
     *
     * @param string $state
     * @param string $redirect_url
     * @param array $scope
     * @param array $params
     * @return string
     */
    public function generateAuthoriseUrl($state = null, string $redirect_url = null, array $scope = [], array $params = []): string
    {
        // Check if redirect_url is a url - the redirect_url should go to a PHP script on the same domain that runs OAuth2::getAccessTokenFromCode()
        if (!filter_var($redirect_url, FILTER_VALIDATE_URL)) throw new Exception('$redirect_url must be a valid URL.');

        if ($state) {
            $state = $this->generateOrPushState($state);
        }

        $default_params = [
            'response_type' => 'code',
            'client_id' => $this->client_id,
            'redirect_uri' => $redirect_url,
            'scope' => implode($this->scope_separator, $scope),
            'state' => (string)$state,
        ];

        return $this->authorise_endpoint . (strpos($this->authorise_endpoint, '?') !== false ? '&' : '?')
            . http_build_query(array_merge($default_params, $params));
    }

    /**
     * Generate a URL to redirect users to to authorise this client with a state.
     *
     * @param string $redirect_url
     * @param array $scope
     * @param array $params
     * @return string
     */
    public function generateAuthoriseUrlAndState(string $redirect_url = null, array $scope = [], array $params = []): string
    {
        // Generate a unique state parameter and store it in the session
        $state = $this->generateState();

        return $this->generateAuthoriseUrl($state, $redirect_url, $scope, $params);
    }

    /**
     * Redirect the user to the URL to authorise this client.
     *
     * @param string $redirect_url
     * @param array $scope
     * @param array $params
     */
    public function redirectToAuthoriseEndpoint(string $redirect_url = null, array $scope = [], array $params = [])
    {
        $url = $this->generateAuthoriseUrlAndState($redirect_url, $scope, $params);

        // Make sure headers have not been sent
        if (headers_sent()) throw new Exception('Headers have already been sent.');

        // Redirect to the Login Dialog
        header('Location: ' . $url, true, 303);
    }

    // https://tools.ietf.org/html/rfc6749#section-4.1.2.1
    protected function handleErrorFromOAuthAuthoriseRequest(array $request, Throwable $previous = null)
    {
        switch ($request['error']) {
            default:
                throw OAuthException::fromRequest($request, $previous);
            case 'invalid_request':
                throw InvalidRequestException::fromRequest($request, $previous);
            case 'unauthorized_client':
                throw UnauthorisedClientException::fromRequest($request, $previous);
            case 'access_denied':
                throw AccessDeniedException::fromRequest($request, $previous);
            case 'unsupported_response_type':
                throw UnsupportedResponseTypeException::fromRequest($request, $previous);
            case 'invalid_scope':
                throw InvalidScopeException::fromRequest($request, $previous);
            case 'server_error':
                throw ServerErrorException::fromRequest($request, $previous);
            case 'temporarily_unavailable':
                throw TemporarilyUnavailableException::fromRequest($request, $previous);
        }
    }
}
