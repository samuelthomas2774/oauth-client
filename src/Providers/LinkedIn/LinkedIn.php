<?php

namespace OAuth2\Providers\LinkedIn;

use OAuth2\OAuth;
use OAuth2\UserProfilesInterface;
use OAuth2\UserPicturesInterface;
use OAuth2\UserProfile;

use OAuth2\Providers\LinkedIn\UserProfile as LinkedInUserProfile;

use stdClass;
use Psr\Http\Message\ResponseInterface;

class LinkedIn extends OAuth implements UserProfilesInterface, UserPicturesInterface
{
    /**
     * Session prefix.
     *
     * @var string
     */
    public $session_prefix = 'linkedin_';

    /**
     * Base API URL.
     *
     * @var string
     */
    public $base_api_endpoint = 'https://api.linkedin.com/v1/';

    /**
     * OAuth 2.0 authorise endpoint.
     *
     * @var string
     */
    public $authorise_endpoint = 'https://www.linkedin.com/uas/oauth2/authorization';

    /**
     * OAuth 2.0 token endpoint.
     *
     * @var string
     */
    public $token_endpoint = 'https://www.linkedin.com/uas/oauth2/accessToken';

    public function __construct(string $client_id, string $client_secret, $token = null, array $options = [])
    {
        $this->api_headers['X-Li-Format'] = 'json';

        parent::__construct($client_id, $client_secret, $token, $options);
    }

    /**
     * Returns the current user.
     *
     * @return \OAuth2\Providers\LinkedIn\UserProfile
     */
    public function getUserProfile(): UserProfile
    {
        $response = $this->api('GET', 'people/~');

        $user = new LinkedInUserProfile(isset($response->id) ? $response->id : '');

        $user->response = $response;
        $user->name = isset($response->firstName) ? $response->firstName . (isset($response->lastName) ? ' ' . $response->lastName : '') : null;
        $user->url = $response->siteStandardProfileRequest->url;

        if (isset($response->emailAddress)) $user->email_addresses = [$response->emailAddress];

        return $user;
    }

    /**
     * Returns the URL of the current user's picture.
     *
     * @return string
     */
    public function getUserPictureUrl(int $size = 50): ?string
    {
        $response = $this->api('GET', 'people/~:(id,pictureUrl)');

        if (!isset($response->pictureUrl)) return null;

        return $response->pictureUrl;
    }
}
