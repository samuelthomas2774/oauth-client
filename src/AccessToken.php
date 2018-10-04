<?php

/**
 * An access token, and in some cases a refresh token.
 */

namespace OAuth2;

use JsonSerializable;

class AccessToken implements JsonSerializable
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
    private $scope = [];

    public function __construct(string $access_token, string $refresh_token = null, int $expires = null, array $scope = [])
    {
        $this->access_token = $access_token;
        $this->refresh_token = $refresh_token;

        $this->expires = $expires;
        $this->scope = $scope;
    }

    public function getAccessToken(): string
    {
        return $this->access_token;
    }

    public function getRefreshToken(): ?string
    {
        return $this->refresh_token;
    }

    public function getExpirationTimestamp(): int
    {
        return $this->expires;
    }

    /**
     * Checks if the access token has expired.
     * It is assumed that the access token is still valid if the expiration
     * timestamp is not available.
     *
     * @return boolean
     */
    public function hasExpired(): bool
    {
        if (!is_int($this->expires)) return false;

        return $this->expires < time();
    }

    /**
     * Get the number of seconds until the access token expires.
     *
     * @return integer
     */
    public function getExpiresIn(): ?int
    {
        if (!is_int($this->expires)) return null;

        return $this->hasExpired() ? 0 : $this->expires - time();
    }

    public function getScope(): array
    {
        return $this->scope;
    }

    public function hasScope(string $scope_token): bool
    {
        return in_array($scope_token, $this->scope);
    }

    public function __toString(): string
    {
        return $this->access_token;
    }

    static public function __set_state(array $state)
    {
        $refresh_token = isset($state['refresh_token']) ? $state['refresh_token'] : null;
        $expires = isset($state['expires']) ? $state['expires'] : isset($state['expires_in']) : time() + $state['expires_in'] : null;
        $scope = isset($state['scope']) ? is_array($state['scope']) ? $state['scope'] : explode(' ', $state['scope']) : null;

        $token = new self($state['access_token'], $refresh_token, $expires, $scope);

        if (isset($state['response'])) $token->response = $state['response'];
        if (isset($state['requested_scope'])) $token->response = $state['requested_scope'];

        return $token;
    }

    public function jsonSerialize()
    {
        $token = [
            'access_token' => $this->access_token,
            'token_type' => 'Bearer',
        ];

        if (isset($this->expires)) {
            $token['expires_in'] = $this->getExpiresIn();
        }

        if (isset($this->refresh_token)) {
            $token['refresh_token'] = $this->refresh_token;
        }

        $token['scope'] = implode(' ', $this->scope);

        return $token;
    }
}
