<?php

/**
 * Session handler.
 */

namespace OAuth2;

interface SessionHandlerInterface
{
    /**
     * Check if sessions are enabled.
     *
     * @return boolean
     */
    public function enabled(): boolean;

    /**
     * Get session data.
     *
     * @param string $key
     * @return
     */
    public function get(string $key);

    /**
     * Set session data.
     *
     * @param string $key
     * @param $value
     * @return boolean
     */
    public function set(string $key, $value): boolean;

    /**
     * Delete session data.
     *
     * @param string $key
     * @return boolean
     */
    public function delete(string $key): boolean;
}
