<?php

/**
 * Session handler.
 */

namespace OAuth2;

class DefaultSessionHandler implements SessionHandlerInterface
{
    /**
     * Check if sessions are enabled.
     *
     * @return boolean
     */
    public function enabled(): bool
    {
        return session_status() === PHP_SESSION_ACTIVE;
    }

    /**
     * Get session data.
     *
     * @param string $key
     * @return
     */
    public function get(string $key)
    {
        return isset($_SESSION[$key]) ? $_SESSION[$key] : null;
    }

    /**
     * Set session data.
     *
     * @param string $key
     * @param $value
     * @return boolean
     */
    public function set(string $key, $value): bool
    {
        $_SESSION[$key] = $value;
        return true;
    }

    /**
     * Delete session data.
     *
     * @param string $key
     * @return boolean
     */
    public function delete(string $key): bool
    {
        if (isset($_SESSION[$key])) unset($_SESSION[$key]);
        return true;
    }
}
