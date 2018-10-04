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
    public function generateAuthoriseUrl($state = null, string $redirect_url = null, array $scope = [], array $params = []): string;

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
     * Returns the state matching the request query string parameter.
     *
     * @return \OAuth2\State
     */
    public function getRequestState(): ?State;

    /**
     * Redirect the user to the URL to authorise this client.
     *
     * @param string $redirect_url
     * @param array $scope
     * @param array $params
     */
    public function redirectToAuthoriseEndpoint(string $redirect_url = null, array $scope = [], array $params = []);
}
