<?php

namespace OAuth2\Grants;

use OAuth2\AuthoriseUrl;
use OAuth2\AuthoriseEndpointInterface;

interface ImplicitGrantInterface extends AuthoriseEndpointInterface
{
    /**
     * Generate a URL to redirect users to to authorise this client.
     *
     * @param string $redirect_url
     * @param array $scope
     * @param array $params
     * @return \OAuth2\AuthoriseUrl
     */
    public function generateImplicitAuthoriseUrl(string $redirect_url = null, array $scope = [], array $params = []): AuthoriseUrl;

    /**
     * Redirect the user to the URL to authorise this client.
     *
     * @param string $redirect_url
     * @param array $scope
     * @param array $params
     */
    public function redirectToImplicitAuthoriseEndpoint(string $redirect_url = null, array $scope = [], array $params = []);
}
