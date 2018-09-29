<?php

namespace OAuth2\ProviderUserProfiles;

use OAuth2\UserProfile;

class GitHub extends UserProfile
{
    /**
     * The original response from the provider's API.
     *
     * @var \stdClass
     */
    public $response;
}
