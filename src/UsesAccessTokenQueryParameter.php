<?php

namespace OAuth2;

trait UsesAccessTokenQueryParameter
{
    // public $access_token_parameter_name = 'access_token';

    /**
     * Returns the request options with an access_token query string parameter with the access token.
     *
     * @param string $method
     * @param string $url
     * @param array $options
     * @param \OAuth2\AccessToken $token
     * @return array $options
     */
    protected function authenticateAccessTokenToApiRequestOptions(string $method, string $url, array $options, AccessToken $token): array
    {
        if (!isset($options['query']) || !is_array($options['query'])) $options['query'] = [];

        $parameter_name = isset($this->access_token_parameter_name) ? $this->access_token_parameter_name : 'access_token';

        $options['query'][$parameter_name] = $token->getAccessToken();

        return $options;
    }
}
