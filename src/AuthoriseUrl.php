<?php

namespace OAuth2;

class AuthoriseUrl
{
    /**
     * OAuth 2.0 authorise endpoint.
     *
     * @var string
     */
    private $authorise_endpoint;

    /**
     * An array of query string parameters.
     *
     * @var array
     */
    private $params = [];

    /**
     * The state included in the URL.
     *
     * @var \OAuth2\State
     */
    private $state;

    /**
     * The requested scope.
     *
     * @var array
     */
    private $scope = [];

    /**
     * The generated URL.
     *
     * @var string
     */
    private $url;

    public function __construct(string $authorise_endpoint, array $params, State $state = null, array $scope = [])
    {
        $this->authorise_endpoint = $authorise_endpoint;
        $this->params = $params;
        $this->state = $state;
        $this->scope = $scope;

        $this->url = $this->authorise_endpoint . (strpos($this->authorise_endpoint, '?') !== false ? '&' : '?')
            . http_build_query($this->params);
    }

    /**
     * Returns the generated URL.
     *
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    public function __toString()
    {
        return $this->url;
    }

    /**
     * Returns the OAuth 2.0 authorise endpoint.
     *
     * @return string
     */
    public function getAuthoriseEndpoint(): string
    {
        return $this->authorise_endpoint;
    }

    /**
     * Returns the query string parameters.
     *
     * @return array
     */
    public function getParams(): array
    {
        return $this->params;
    }

    /**
     * Returns the response_type query string parameter.
     *
     * @return string
     */
    public function getResponseType(): string
    {
        return $this->params['response_type'];
    }

    /**
     * Returns the client_id query string parameter.
     *
     * @return string
     */
    public function getClientId(): string
    {
        return $this->params['client_id'];
    }

    /**
     * Returns the redirect_uri query string parameter.
     *
     * @return string
     */
    public function getRedirectUrl(): string
    {
        return $this->params['redirect_uri'];
    }

    /**
     * Returns the requested scope.
     *
     * @return array
     */
    public function getScope(): string
    {
        return $this->scope;
    }

    /**
     * Returns the state.
     *
     * @return \OAuth2\State
     */
    public function getState(): ?State
    {
        return $this->state;
    }

    /**
     * Returns the state.
     *
     * @return string
     */
    public function getStateId(): string
    {
        return $this->params['state'];
    }
}
