<?php

namespace OAuth2\Providers\Discord;

use OAuth2\OAuth;
use OAuth2\UserProfile;
use OAuth2\UserProfilesInterface;
use OAuth2\UserPicturesInterface;

use OAuth2\Providers\Discord\UserProfile as DiscordUserProfile;

class Discord extends OAuth implements UserProfilesInterface, UserPicturesInterface
{
    /**
     * Session prefix.
     *
     * @var string
     */
    public $session_prefix = 'discord_';

    /**
     * Base API URL.
     *
     * @var string
     */
    public $base_api_endpoint = 'https://discordapp.com/api/';

    /**
     * OAuth 2.0 authorise endpoint.
     *
     * @var string
     */
    public $authorise_endpoint = 'https://discordapp.com/api/oauth2/authorize';

    /**
     * OAuth 2.0 token endpoint.
     *
     * @var string
     */
    public $token_endpoint = 'oauth2/token';

    /**
     * Discord permissions.
     *
     * @var array
     */
    public static $permissions = [
        'CREATE_INSTANT_INVITE' => 0x00000001,
        'KICK_MEMBERS'          => 0x00000002,
        'BAN_MEMBERS'           => 0x00000004,
        'ADMINISTRATOR'         => 0x00000008,
        'MANAGE_CHANNELS'       => 0x00000010,
        'MANAGE_GUILD'          => 0x00000020,
        'ADD_REACTIONS'         => 0x00000040,
        'VIEW_AUDIT_LOG'        => 0x00000080,
        'VIEW_CHANNEL'          => 0x00000400,
        'SEND_MESSAGES'         => 0x00000800,
        'SEND_TTS_MESSAGES'     => 0x00001000,
        'MANAGE_MESSAGES'       => 0x00002000,
        'EMBED_LINKS'           => 0x00004000,
        'ATTACH_FILES'          => 0x00008000,
        'READ_MESSAGE_HISTORY'  => 0x00010000,
        'MENTION_EVERYONE'      => 0x00020000,
        'USE_EXTERNAL_EMOJIS'   => 0x00040000,
        'CONNECT'               => 0x00100000,
        'SPEAK'                 => 0x00200000,
        'MUTE_MEMBERS'          => 0x00400000,
        'DEAFEN_MEMBERS'        => 0x00800000,
        'MOVE_MEMBERS'          => 0x01000000,
        'USE_VAD'               => 0x02000000,
        'CHANGE_NICKNAME'       => 0x04000000,
        'MANAGE_NICKNAMES'      => 0x08000000,
        'MANAGE_ROLES'          => 0x10000000,
        'MANAGE_WEBHOOKS'       => 0x20000000,
        'MANAGE_EMOJIS'         => 0x40000000
    ];

    /**
     * Returns the current user.
     *
     * @return \OAuth2\Providers\Discord\UserProfile
     */
    public function getUserProfile(): UserProfile
    {
        $response = $this->api('GET', 'users/@me');

        $user = new DiscordUserProfile(isset($response->id) ? $response->id : '');

        $user->response = $response;
        $user->username = $response->username . '#' . $response->discriminator;
        $user->name = $response->username;
        $user->email_addresses = [$response->email];

        return $user;
    }

    /**
     * Returns the URL of the current user's picture.
     *
     * @param integer $size
     * @param string $type Either "png", "jpg", "webp" or "gif"
     * @return string
     */
    public function getUserPictureUrl(int $size = 50, string $type = 'png'): string
    {
        $size = min(max($size, 16), 2048);
        $size = 2 ** (ceil(log($size - 1, 2)) + 1);

        $response = $this->api('GET', 'users/@me');

        return 'https://cdn.discordapp.com/avatars/' . $response->id . '/' . $response->avatar . '.' . $type
            . ($size ? '?size=' . $size : '');
    }

    /**
     * Converts an array of permissions to an integer.
     *
     * @param array|string $permissions
     * @return integer
     */
    public function permissionsToInteger($permissions): int
    {
        if (is_string($permissions)) $permissions = [self::$permissions[$permissions]];

        $integer = 0;

        foreach ($permissions as $permission) {
            $integer = $integer | self::$permissions[$permission];
        }

        return $integer;
    }

    /**
     * Converts an integer to an array of permissions.
     *
     * @param integer $integer
     * @param integer &$left
     * @return array
     */
    public function integerToPermissions(int $integer, &$left = null): array
    {
        $permissions = [];
        $left = $integer;

        foreach (self::$permissions as $permission => $bitmask) {
            if ($integer & $bitmask) {
                array_push($permissions, $permission);
                $left = $left ^ $bitmask;
            }
        }

        // if ($left != 0 && func_num_args() < 2) {
        //     throw new Exception(__METHOD__ . ": \$integer was not a valid permissions integer. {$left} remaining.");
        // }

        return $permissions;
    }

    /**
     * Checks if a permissions integer has a permission.
     *
     * @param integer $integer
     * @param string $permission
     */
    public function integerHasPermission($integer, $permission)
    {
        return $integer & self::$permissions[$permission];
    }

    /**
     * Generates a URL to add a bot to a guild.
     *
     * @param array|integer $permissions
     * @param string $redirect_url
     * @return string
     */
    public function inviteBot($permissions, string $redirect_url = null): string
    {
        if (is_array($permissions)) $permissions = $this->permissionsToInteger($permissions);

        return $this->generateAuthoriseUrlAndState($redirect_url, ['bot'], [
            'permissions' => $permissions,
        ]);
    }

    /**
     * Generates a URL to add a webhook to a guild.
     *
     * @param string $redirect_url
     * @return string
     */
    public function inviteWebhook(string $redirect_url = null): string
    {
        return $this->generateAuthoriseUrlAndState($redirect_url, ['webhook.incoming']);
    }
}
