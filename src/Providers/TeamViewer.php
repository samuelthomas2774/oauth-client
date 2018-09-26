<?php

namespace OAuth2\Providers;

use OAuth2\OAuth;
use OAuth2\UserProfilesInterface;

class TeamViewer extends OAuth implements UserProfilesInterface
{
    /**
     * Session prefix.
     *
     * @var string
     */
    public $session_prefix = 'teamviewer_';

    /**
     * Base API URL.
     *
     * @var string
     */
    public $base_api_endpoint = 'https://webapi.teamviewer.com/api/v1/';

    /**
     * OAuth 2.0 authorise endpoint.
     *
     * @var string
     */
    public $authorise_endpoint = 'https://webapi.teamviewer.com/api/v1/oauth2/authorize';

    /**
     * OAuth 2.0 token endpoint.
     *
     * @var string
     */
    public $token_endpoint = 'oauth2/token';

    /**
     * Scope separator.
     * This *should* be " " to be compliant with the OAuth 2.0 specification, however
     * some providers use "," instead.
     *
     * @var string
     */
    public $scope_separator = ',';

    /**
     * Returns the current user.
     *
     * @return \stdClass
     */
    public function getUserProfile()
    {
        $response = $this->api('GET', 'account');

        $response->id = $response->userid;

        return $response;
    }
}
