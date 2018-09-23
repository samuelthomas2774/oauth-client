<?php

namespace OAuth2;

interface UserProfilesInterface
{
    /**
     * Returns the current user.
     *
     * @return mixed
     */
    public function getUserProfile();
}
