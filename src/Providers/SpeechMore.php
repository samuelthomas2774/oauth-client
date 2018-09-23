<?php

namespace OAuth2\Providers;

use OAuth2\OAuth;
use OAuth2\UserProfilesInterface;
use OAuth2\UserPicturesInterface;

class SpeechMore extends OAuth implements UserProfilesInterface, UserPicturesInterface
{
    /**
     * Base API URL.
     *
     * @var string
     */
    public $base_api_endpoint = 'https://speechmore.ml/api/';

    /**
     * OAuth 2.0 authorise endpoint.
     *
     * @var string
     */
    public $authorise_endpoint = 'https://speechmore.ml/oauth/authorize';

    /**
     * OAuth 2.0 token endpoint.
     *
     * @var string
     */
    public $token_endpoint = '/oauth/token';

    /**
     * Returns the current user.
     *
     * @return \stdClass
     */
    public function getTokenInfo()
    {
        return $this->api('GET', 'token');
    }

    /**
     * Returns the current user.
     *
     * @return \stdClass
     */
    public function getUserProfile()
    {
        return $this->api('GET', 'user');
    }

    /**
     * Returns the URL of the current user's picture.
     *
     * @param integer $size
     * @return string
     */
    public function getUserPictureUrl(int $size = null): string
    {
        $user = $this->getUserProfile();

        return $user->avatar_url;
    }
}
