<?php

namespace OAuth2;

interface UserPicturesInterface
{
    /**
     * Returns the URL of the current user's picture.
     *
     * @param integer $size
     * @return string
     */
    public function getUserPictureUrl(int $size = 50): ?string;
}
