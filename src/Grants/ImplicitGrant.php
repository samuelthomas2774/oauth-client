<?php

namespace OAuth2\Grants;

use OAuth2\AuthoriseUrl;

trait ImplicitGrant
{
    public function generateImplicitAuthoriseUrl(string $redirect_url = null, array $scope = [], array $params = []): AuthoriseUrl
    {
        if (!isset($params['response_type'])) $params['response_type'] = 'token';

        return $this->generateAuthoriseUrl(null, $redirect_url, $scope, $params);
    }

    public function redirectToImplicitAuthoriseEndpoint(string $redirect_url = null, array $scope = [], array $params = []): AuthoriseUrl
    {
        if (!isset($params['response_type'])) $params['response_type'] = 'token';

        return $this->redirectToAuthoriseEndpoint(null, $redirect_url, $scope, $params);
    }

    // \OAuth2\AuthoriseEndpoint
    abstract protected function generateAuthoriseUrl($state = null, string $redirect_url = null, array $scope = [], array $params = []): AuthoriseUrl;
}
