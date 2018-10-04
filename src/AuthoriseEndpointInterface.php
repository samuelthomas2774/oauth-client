<?php

namespace OAuth2;

interface AuthoriseEndpointInterface
{
    /**
     * Generate a URL to redirect users to to authorise this client.
     *
     * @param string $state
     * @param string $redirect_url
     * @param array $scope
     * @param array $params
     * @return string
     */
    public function generateAuthoriseUrl(string $state = null, string $redirect_url = null, array $scope = [], array $params = []): string;

    /**
     * Generate a URL to redirect users to to authorise this client with a state.
     *
     * @param string $redirect_url
     * @param array $scope
     * @param array $params
     * @return string
     */
    public function generateAuthoriseUrlAndState(string $redirect_url = null, array $scope = [], array $params = []): string;

    /**
     * Generate a state.
     *
     * @param boolean $update_session
     * @return string
     */
    public function getState(bool $update_session = true): string;

    /**
     * Redirect the user to the URL to authorise this client.
     *
     * @param string $redirect_url
     * @param array $scope
     * @param array $params
     */
    public function redirectToAuthoriseEndpoint(string $redirect_url = null, array $scope = [], array $params = []);
}
