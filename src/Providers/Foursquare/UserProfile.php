<?php

namespace OAuth2\Providers\Foursquare;

use OAuth2\UserProfile as BaseUserProfile;

class UserProfile extends BaseUserProfile
{
    /**
     * The original response from the provider's API.
     *
     * @var \stdClass
     */
    public $response;
}
