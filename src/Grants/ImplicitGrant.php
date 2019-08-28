<?php

namespace OAuth2\Grants;

use OAuth2\AuthoriseUrl;

trait ImplicitGrant
{
    /**
     * Generate a URL to redirect users to to authorise this client.
     *
     * @param string $redirect_url
     * @param array $scope
     * @param array $params
     * @return \OAuth2\AuthoriseUrl
     */
    public function generateImplicitAuthoriseUrl(string $redirect_url = null, array $scope = [], array $params = []): AuthoriseUrl
    {
        if (!isset($params['response_type'])) $params['response_type'] = 'token';

        return $this->generateAuthoriseUrl(null, $redirect_url, $scope, $params);
    }

    /**
     * Redirect the user to the URL to authorise this client.
     *
     * @param string $redirect_url
     * @param array $scope
     * @param array $params
     */
    public function redirectToImplicitAuthoriseEndpoint(string $redirect_url = null, array $scope = [], array $params = [])
    {
        if (!isset($params['response_type'])) $params['response_type'] = 'token';

        return $this->redirectToAuthoriseEndpoint(null, $redirect_url, $scope, $params);
    }

    // \OAuth2\AuthoriseEndpoint
    abstract protected function generateAuthoriseUrl($state = null, string $redirect_url = null, array $scope = [], array $params = []): AuthoriseUrl;
    abstract protected function redirectToAuthoriseEndpoint(string $redirect_url = null, array $scope = [], array $params = []);
}
