<?php

namespace OAuth2\Grants;

trait ImplicitGrant
{
    public function generateImplicitAuthoriseUrl(string $redirect_url = null, array $scope = [], array $params = []): string
    {
        if (!isset($params['response_type'])) $params['response_type'] = 'token';

        return $this->generateAuthoriseUrl(null, $redirect_url, $scope, $params);
    }

    public function redirectToImplicitAuthoriseEndpoint(string $redirect_url = null, array $scope = [], array $params = []): string
    {
        if (!isset($params['response_type'])) $params['response_type'] = 'token';

        return $this->redirectToAuthoriseEndpoint(null, $redirect_url, $scope, $params);
    }

    // \OAuth2\AuthoriseEndpoint
    abstract function generateAuthoriseUrl(string $state = null, string $redirect_url = null, array $scope = [], array $params = []): string;
}
