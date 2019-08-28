<?php

namespace OAuth2;

interface UserProfilesInterface
{
    /**
     * Returns the current user.
     *
     * @return \OAuth2\UserProfile
     */
    public function getUserProfile(): UserProfile;
}
