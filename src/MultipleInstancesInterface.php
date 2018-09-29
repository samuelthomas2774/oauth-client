<?php

namespace OAuth2;

interface MultipleInstancesInterface
{
    /**
     * Sets the instance URL of the client.
     *
     * @param string $instance_url
     */
    public function setInstanceUrl(string $instance_url, bool $update_session_prefix = true);
}
