<?php

namespace OAuth2\Providers\Facebook;

use OAuth2\OAuth;
use OAuth2\AccessToken;
use OAuth2\UserProfile;
use OAuth2\UserProfilesInterface;
use OAuth2\UserPicturesInterface;

use OAuth2\AuthoriseEndpoint;
use OAuth2\TokenEndpoint;
use OAuth2\AuthoriseEndpointInterface;
use OAuth2\TokenEndpointInterface;

use OAuth2\Grants\AuthorisationCodeGrant;
use OAuth2\Grants\ImplicitGrant;
use OAuth2\Grants\ClientCredentialsGrant;

use OAuth2\Grants\AuthorisationCodeGrantInterface;
use OAuth2\Grants\ImplicitGrantInterface;
use OAuth2\Grants\ClientCredentialsGrantInterface;

use OAuth2\Exceptions\ApiException;

use OAuth2\Providers\Facebook\UserProfile as FacebookUserProfile;

use Psr\Http\Message\ResponseInterface;
use stdClass;
use Exception;

class Facebook extends OAuth implements UserProfilesInterface, UserPicturesInterface, AuthoriseEndpointInterface, TokenEndpointInterface, AuthorisationCodeGrantInterface, ImplicitGrantInterface, ClientCredentialsGrantInterface
{
    use AuthorisationCodeGrant;
    use ImplicitGrant;
    use ClientCredentialsGrant;

    /**
     * Session prefix.
     *
     * @var string
     */
    public $session_prefix = 'facebook_';

    /**
     * Base API URL.
     *
     * @var string
     */
    public $base_api_endpoint = 'https://graph.facebook.com/v3.1/';

    /**
     * OAuth 2.0 authorise endpoint.
     *
     * @var string
     */
    public $authorise_endpoint = 'https://www.facebook.com/v3.1/dialog/oauth';

    /**
     * OAuth 2.0 token endpoint.
     *
     * @var string
     */
    public $token_endpoint = 'oauth/access_token';

    /**
     * Token debug endpoint.
     *
     * @var string
     */
    public $token_debug_endpoint = 'debug_token';

    protected function getApiResponse(string $method, string $url, array $options, ResponseInterface $response)
    {
        $data = json_decode($response->getBody());

        if (isset($data->error)) {
            throw new ApiException($data->error);
        }

        return $data;
    }

    /**
     * Parses a signed request from Facebook.
     *
     * @param string $signed_request
     * @return \stdClass
     */
    public function parseSignedRequest(string $signed_request = null)
    {
        if (!is_string($signed_request)) {
            if (!isset($_POST['signed_request'])) {
                throw new Exception('Missing signed_request.');
            }

            $signed_request = trim($_POST['signed_request']);
        }

        list($encoded_sig, $payload) = explode('.', $signed_request, 2);

        // Decode the data
        $sig = base64_decode(strtr($encoded_sig, '-_', '+/'));
        $data = json_decode(base64_decode(strtr($payload, '-_', '+/')), false);

        // Confirm the signature
        $expected_sig = hash_hmac('sha256', $payload, $this->client_secret, true);

        if ($sig !== $expected_sig) {
            throw new Exception('Invalid signature');
        }

        return $data;
    }

    /**
     * Checks if an access token is valid.
     *
     * @param \OAuth2\AccessToken $token
     * @return \stdClass
     */
    public function validateAccessToken($token = null)
    {
        if ($token instanceof AccessToken) $token = $token->getAccessToken();
        if (!is_string($token)) throw new Exception('$token must be an \OAuth2\AccessToken, a string or null.');

        // Example request: GET /oauth/token/debug?access_token={access_token}
        $response = $this->api('GET', $this->token_debug_endpoint, [
            'access_token' => $this->client_id . '|' . $this->client_secret,
            'input_token' => $token,
        ], false);

        return $response && $response->expires_in > 0;
    }

    /**
     * Returns the current user.
     *
     * @param array $fields
     * @return \OAuth2\Providers\Facebook\UserProfile
     */
    public function getUserProfile(array $fields = []): UserProfile
    {
        $fields = array_merge($fields, ['id', 'name', 'email']);

        $response = $this->api('GET', 'me', [
            'query' => ['fields' => implode(',', $fields)],
        ]);

        $user = new FacebookUserProfile(isset($response->id) ? $response->id : '');

        $user->response = $response;

        if (isset($response->name)) $user->name = $response->name;
        if (isset($response->email)) $user->email_addresses = [$response->email];

        return $user;
    }

    /**
     * Returns the URL of the current user's picture.
     *
     * @param integer $size
     * @return string
     */
    public function getUserPictureUrl(int $size = 50): ?string
    {
        $response = $this->api('GET', 'me', [
            'query' => ['fields' => 'id,picture.width(' . $size . ').height(' . $size . ')'],
        ]);

        if (!isset($response->picture->data) || !isset($response->picture->data->url)) return null;

        return $response->picture->data->url;
    }

    /**
     * Returns an array of the permissions requested by the client.
     *
     * @param boolean $rearrange
     * @return \stdClass[]
     */
    public function permissions(bool $rearrange = true): array
    {
        $response = $this->api('GET', 'me/permissions');

        if (!$rearrange) return $response;

        $permissions = [];

        foreach ($response->data as $p) {
            $permission = $permissions[$data->permission] = new stdClass();
            $permission->name = $data->permission;
            $permission->granted = $data->status === 'granted';
            $permission->status = $data->status;
        }

        return $permissions;
    }

    /**
     * Checks if a scope has been granted.
     *
     * @param string $scope
     * @return boolean
     */
    public function permission(string $scope): bool
    {
        $permissions = $this->permissions();

        return isset($permissions[$scope]) && $permissions[$scope]->granted;
    }

    /**
     * Returns an array of the user's IDs for other apps linked to the same business.
     *
     * @param boolean $rearrange
     * @return \stdClass[]
     */
    public function ids(bool $rearrange = true): array
    {
        $response = $this->api('GET', 'me/ids_for_business');

        if (!$rearrange) return $response;

        $ids = [];

        foreach ($response->data as $data) {
            $id = $ids[$id->app->id] = new stdClass();
            $id->app_name = $data->app->name;
            $id->app_namespace = $data->app->namespace;
            $id->app_id = $data->app->id;
            $id->user_id = $data->id;
        }

        return $ids;
    }

    /**
     * Revokes the access token or one scope.
     *
     * @param string $scope
     * @return boolean
     */
    public function deauth(string $scope = null): bool
    {
        $response = $this->api('DELETE', 'me/permissions' . (is_string($permission) ? '/' . urlencode($permission) : ''));

        return isset($response->success) && $response->success;
    }

    /**
     * Returns an array of the pages the user manages.
     * Requires the "manage_pages" scope.
     *
     * @param boolean $rearrange
     * @return \stdClass[]
     */
    public function pages(bool $rearrange = true): array
    {
        if (!$this->permission('manage_pages')) {
            throw new Exception('User has declined the manage_pages permission.');
        }

        $response = $this->api('GET', 'me/accounts');

        if (!$rearrange) return $response;

        $pages = [];

        foreach ($response->data as $data) {
            $page = $pages[$data->id] = new stdClass();
            $page->id = $data->id;
            $page->name = $data->name;
            $page->access_token = new AccessToken($data->access_token);
            $page->permissions = $data->perms;
            $page->category = $data->category;
            $page->category_list = isset($data->category_list) ? $data->category_list : null;
            $page->oauth = new self($this->client_id, $this->client_secret, $page->access_token);
        }

        return $pages;
    }

    /**
     * Posts a message to the user's timeline.
     * Requires the "publish_actions" scope.
     *
     * @param string $message
     * @param array $data
     * @return string
     */
    public function post(string $message, array $post = [])
    {
        if (!$this->permission('publish_actions')) {
            throw new Exception('User has declined the publish_actions permission.');
        }

        $post['message'] = $message;

        $response = $this->api('POST', 'me/feed', [
            'form_params' => $post,
        ]);

        return $response->id;
    }
}
