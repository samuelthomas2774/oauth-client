<?php

namespace OAuth2\Providers\Slack;

use OAuth2\OAuth;
use OAuth2\AccessToken;
use OAuth2\UserProfile;
use OAuth2\UserProfilesInterface;
use OAuth2\UserPicturesInterface;

use OAuth2\AuthoriseEndpoint;
use OAuth2\TokenEndpoint;
use OAuth2\AuthoriseEndpointInterface;

use OAuth2\Grants\AuthorisationCodeGrant;
use OAuth2\Grants\AuthorisationCodeGrantInterface;

use OAuth2\Providers\Slack\UserProfile as SlackUserProfile;

class Slack extends OAuth implements UserProfilesInterface, UserPicturesInterface, AuthoriseEndpointInterface, AuthorisationCodeGrantInterface
{
    use AuthoriseEndpoint;
    use TokenEndpoint;

    use AuthorisationCodeGrant;

    /**
     * Session prefix.
     *
     * @var string
     */
    public $session_prefix = 'slack_';

    /**
     * Base API URL.
     *
     * @var string
     */
    public $base_api_endpoint = 'https://slack.com/api/';

    /**
     * OAuth 2.0 authorise endpoint.
     *
     * @var string
     */
    public $authorise_endpoint = 'https://slack.com/oauth/authorize/';

    /**
     * OAuth 2.0 token endpoint.
     *
     * @var string
     */
    public $token_endpoint = 'oauth.access';

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
     * @return \OAuth2\Providers\Slack\UserProfile
     */
    public function getUserProfile(): UserProfile
    {
        $response = $this->api('GET', 'users.identity');

        $user = new SlackUserProfile($response->team->id . ':' . $response->user->id);

        $user->response = $response;
        $user->name = $response->user->name;
        $user->email_addresses = [$response->user->email];

        return $user;
    }

    /**
     * Returns the URL of the current user's picture.
     *
     * @param integer $size
     * @return string
     */
    public function getUserPictureUrl(int $size = 50): string
    {
        $response = $this->api('GET', 'users.identity');

        if ($size > 192 && isset($response->user->image_512)) {
            return $response->user->image_512;
        } elseif ($size > 72 && isset($response->user->image_192)) {
            return $response->user->image_192;
        } elseif ($size > 48 && isset($response->user->image_72)) {
            return $response->user->image_72;
        } elseif ($size > 32 && isset($response->user->image_48)) {
            return $response->user->image_48;
        } elseif ($size > 24 && isset($response->user->image_32)) {
            return $response->user->image_32;
        } elseif (isset($response->user->image_24)) {
            return $response->user->image_24;
        } elseif (isset($response->user->email)) {
            return 'https://gravatar.com/avatar/' . md5($response->user->email) . '?size=' . $size . '&default=https%3A%2F%2Fa.slack-edge.com%2F7fa9%2Fimg%2Favatars%2Fava_0001-512.png';
        } else {
            return 'https://a.slack-edge.com/7fa9/img/avatars/ava_0001-512.png';
        }
    }
}
