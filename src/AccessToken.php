<?php

/**
 * An access token, and in some cases a refresh token.
 */

namespace OAuth2;

class AccessToken
{
    /**
     * The access token.
     *
     * @var string
     */
    private $access_token = null;

    /**
     * The refresh token.
     * This is not available for all providers.
     *
     * @var string
     */
    private $refresh_token = null;

    /**
     * The time the access token will expire.
     *
     * @var int
     */
    private $expires = null;

    /**
     * The access token's scope.
     * This is usually only available when the access token is created.
     *
     * @var array
     */
    private $scope = array();

    public function __construct(string $access_token, string $refresh_token = null, int $expires = null, array $scope = null)
    {
        $this->access_token = $access_token;
        $this->refresh_token = $refresh_token;

        $this->expires = $expires;
        $this->scope = $scope;
    }

    public function getAccessToken()
    {
        return $this->access_token;
    }

    public function getRefreshToken()
    {
        return $this->refresh_token;
    }

    public function getExpirationTimestamp()
    {
        return $this->expires;
    }

    /**
     * Get the number of seconds until the access token expires.
     * It is assumed that the access token is still valid if the expiration
     * timestamp is not available.
     *
     * @return int
     */
    public function hasExpired(): bool
    {
        if (!is_int($this->expires)) return false;

        return $this->expires > time();
    }

    /**
     * Get the number of seconds until the access token expires.
     * It is assumed that the access token will expire in one second if the
     * expiration timestamp is not available.
     *
     * @return int
     */
    public function getExpiresIn()
    {
        if (!is_int($this->expires)) return 1;

        return $this->hasExpired() ? 0 : $this->expires - time();
    }

    public function getScope()
    {
        return $this->scope;
    }

    public function hasScope($scope_token)
    {
        if (!is_array($this->scope)) return null;

        return in_array($scope_token, $this->scope);
    }
}
