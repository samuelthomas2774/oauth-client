<?php

namespace OAuth2\Grants;

use OAuth2\AuthoriseUrl;
use OAuth2\AuthoriseEndpointInterface;

interface ImplicitGrantInterface extends AuthoriseEndpointInterface
{
    public function generateImplicitAuthoriseUrl(string $redirect_url = null, array $scope = [], array $params = []): AuthoriseUrl;

    public function redirectToImplicitAuthoriseEndpoint(string $redirect_url = null, array $scope = [], array $params = []): AuthoriseUrl;
}
