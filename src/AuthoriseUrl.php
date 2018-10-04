<?php

namespace OAuth2;

class AuthoriseUrl
{
    private $authorise_endpoint;
    private $params = [];
    private $state;
    private $scope = [];
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

    public function getUrl(): string
    {
        return $this->url;
    }

    public function __toString()
    {
        return $this->url;
    }

    public function getAuthoriseEndpoint(): string
    {
        return $this->authorise_endpoint;
    }

    public function getParams(): array
    {
        return $this->params;
    }

    public function getResponseType(): string
    {
        return $this->params['response_type'];
    }

    public function getClientId(): string
    {
        return $this->params['client_id'];
    }

    public function getRedirectUrl(): string
    {
        return $this->params['redirect_uri'];
    }

    public function getScope(): string
    {
        return $this->scope;
    }

    public function getState(): string
    {
        return $this->state;
    }

    public function getStateId(): string
    {
        return $this->params['state'];
    }
}
