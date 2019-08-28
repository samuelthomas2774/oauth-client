<?php

namespace OAuth2\Providers\GitLab;

use OAuth2\OAuth;
use OAuth2\AccessToken;
use OAuth2\UserProfile;
use OAuth2\UserProfilesInterface;
use OAuth2\UserPicturesInterface;
use OAuth2\MultipleInstancesInterface;

use OAuth2\AuthoriseEndpoint;
use OAuth2\TokenEndpoint;

use OAuth2\Grants\AuthorisationCodeGrant;
use OAuth2\Grants\AuthorisationCodeGrantInterface;

use OAuth2\Providers\GitLab\UserProfile as GitLabUserProfile;

class GitLab extends OAuth implements UserProfilesInterface, UserPicturesInterface, MultipleInstancesInterface, AuthorisationCodeGrantInterface
{
    use AuthoriseEndpoint;
    use TokenEndpoint;

    use AuthorisationCodeGrant;

    /**
     * Session prefix.
     *
     * @var string
     */
    public $session_prefix = 'gitlab_https_gitlab_com_';

    /**
     * Base API URL.
     *
     * @var string
     */
    public $base_api_endpoint = 'https://gitlab.com/api/v4/';

    /**
     * OAuth 2.0 authorise endpoint.
     *
     * @var string
     */
    public $authorise_endpoint = 'https://gitlab.com/oauth/authorize';

    /**
     * OAuth 2.0 token endpoint.
     *
     * @var string
     */
    public $token_endpoint = '/oauth/token';

    /**
     * Sets the instance URL of the client.
     *
     * @param string $instance_url
     */
    public function setInstanceUrl(string $instance_url, bool $update_session_prefix = true)
    {
        $instance_url = rtrim($instance_url, '/');

        $this->base_api_endpoint = $instance_url . '/api/v4/';
        $this->authorise_endpoint = $instance_url . '/oauth/authorize';
        $this->token_endpoint = $instance_url . '/oauth/token';

        if ($update_session_prefix) {
            $this->session_prefix = 'gitlab_' . preg_replace(['/[^a-z0-9-_.:]/i', '/[-.:]/', '/([^a-zA-Z0-9]{2,})/'], ['', '_', '$1'], $instance_url) . '_';
        }
    }

    /**
     * Returns the current user.
     *
     * @return \OAuth2\Providers\GitLab\UserProfile
     */
    public function getUserProfile(): UserProfile
    {
        $response = $this->api('GET', 'user');

        $user = new GitLabUserProfile(isset($response->id) ? $response->id : '');

        $user->response = $response;
        $user->username = $response->username;
        $user->name = $response->name;
        $user->email_addresses = [$response->email];
        $user->url = $response->web_url;

        return $user;
    }

    /**
     * Returns the URL of the current user's picture.
     *
     * @return string
     */
    public function getUserPictureUrl(int $size = 50): ?string
    {
        $response = $this->api('GET', 'user');

        if (!isset($response->avatar_url)) return null;

        return $response->avatar_url .
            ($size ? (strpos($response->avatar_url, '?') !== false ? '&' : '?') . 'size=' . $size : '');
    }
}
