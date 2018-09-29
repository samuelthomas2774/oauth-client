<?php

namespace OAuth2\Providers\Mastodon;

use OAuth2\OAuth;
use OAuth2\AccessToken;
use OAuth2\UserProfilesInterface;
use OAuth2\UserPicturesInterface;
use OAuth2\UserProfile;

use OAuth2\Providers\Mastodon\UserProfile as MastodonUserProfile;

class Mastodon extends OAuth implements UserProfilesInterface, UserPicturesInterface
{
    /**
     * Session prefix.
     *
     * @var string
     */
    public $session_prefix = 'mastodon_https_mastodon_social_';

    /**
     * Base API URL.
     *
     * @var string
     */
    public $base_api_endpoint = 'https://mastodon.social';

    /**
     * OAuth 2.0 authorise endpoint.
     *
     * @var string
     */
    public $authorise_endpoint = 'https://mastodon.social/oauth/authorize';

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
    public function setInstanceUrl(string $instance_url)
    {
        $instance_url = rtrim($instance_url, '/');

        $this->session_prefix = 'mastodon_' . preg_replace(['/[^a-z0-9-_.:]/i', '/[-.:]/', '/([^a-zA-Z0-9]{2,})/'], ['', '_', '$1'], $instance_url) . '_';
        $this->base_api_endpoint = $instance_url . '/api/v1/';
        $this->authorise_endpoint = $instance_url . '/oauth/authorize';
        $this->token_endpoint = $instance_url . '/oauth/token';
    }

    /**
     * Returns the current user.
     *
     * @return \OAuth2\Providers\Mastodon\UserProfile
     */
    public function getUserProfile(): UserProfile
    {
        $response = $this->api('GET', 'account/verify_credentials');

        $user = new MastodonUserProfile(isset($response->id) ? $response->id : '');

        $user->response = $response;
        $user->username = $response->username;
        $user->name = $response->display_name;
        $user->email_addresses = [$response->email];

        return $user;
        //
        // $user = new stdClass();
        // $user->id = isset($response->id) ? $response->id : null;
        // $user->username = isset($response->username) ? $response->username : $user->id;
        // $user->name = isset($response->display_name) ? $response->display_name : $user->username;
        // $user->email = isset($response->email) ? $response->email : null;
        // $user->response = $response;
        //
        // return $user;
    }

    /**
     * Returns the URL of the current user's picture.
     *
     * @return string
     */
    public function getUserPictureUrl(int $size = 50): ?string
    {
        $response = $this->api('GET', 'account/verify_credentials');

        if (!isset($response->avatar)) return null;

        return $response->avatar;
    }
}
