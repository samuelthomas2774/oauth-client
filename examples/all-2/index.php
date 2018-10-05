<?php

ini_set('display_errors', true);

require_once __DIR__ . '/../../vendor/autoload.php';

use OAuth2\State;
use OAuth2\UserProfilesInterface;
use OAuth2\UserPicturesInterface;

use OAuth2\Grants\RefreshTokenGrantInterface;
use OAuth2\Grants\ResourceOwnerCredentialsGrantInterface;

use OAuth2\Providers\Discord\Discord;

// Start a session
// This must be done after loading the autoloader (so access tokens can be unserialized properly) and before the client is created so the access token can be restored from the session
session_start();

$client_info = require __DIR__ . '/client.php';

if ($client_info) {
    $client = new $client_info['class']($client_info['id'], $client_info['secret'], null, $client_info['options']);

    // Delete the access token if needed
    if (isset($_GET['del_token'])) $client->setAccessToken(null);

    // Output a link to the authorise endpoint
    $authorise_url = $client->generateAuthoriseUrlAndState($client_info['redirect_url'], $client_info['scope']);
    echo '<a href="' . htmlentities($authorise_url) . '">Login to ' . htmlentities($client_info['name']) . '</a><br />';
    echo '<details><summary>Authorise URL:</summary><pre>' . htmlentities(print_r($authorise_url, true)) . '</pre></details>';
    echo '<details><summary>State:</summary><pre>' . htmlentities(print_r($authorise_url->getState(), true)) . '</pre></details>';

    // Output some authorise URLs with state data
    echo '<a href="' . htmlentities($client->generateAuthoriseUrl([
        'link' => 1,
    ], $client_info['redirect_url'], $client_info['scope'])) . '">Login to ' . htmlentities($client_info['name']) . ' (1)</a><br />';

    echo '<a href="' . htmlentities($client->generateAuthoriseUrl([
        'link' => 2,
    ], $client_info['redirect_url'], $client_info['scope'])) . '">Login to ' . htmlentities($client_info['name']) . ' (2)</a><br />';

    echo '<details><summary>Client:</summary><pre>' . htmlentities(print_r($client, true)) . '</pre></details>';

    echo '<a href="' . htmlentities(dirname($_SERVER['SCRIPT_NAME']) . '/index.php' . $_SERVER['PATH_INFO'] . '?del_token=') . '">Delete token</a><br />';

    if ($client instanceof RefreshTokenGrantInterface) {
        echo '<a href="' . htmlentities(dirname($_SERVER['SCRIPT_NAME']) . '/refresh.php' . $_SERVER['PATH_INFO']) . '">Refresh current token</a><br />';

        echo '<form action="' . htmlentities(dirname($_SERVER['SCRIPT_NAME']) . '/refresh.php' . $_SERVER['PATH_INFO']) . '">';
        echo '<input name="refresh_token" type="text" placeholder="Refresh token" />';
        echo '<button type="submit">Submit</button>';
        echo '</form>';
    }

    if ($client instanceof ResourceOwnerCredentialsGrantInterface) {
        echo '<form action="' . htmlentities(dirname($_SERVER['SCRIPT_NAME']) . '/password.php' . $_SERVER['PATH_INFO']) . '">';
        echo '<input name="username" type="text" placeholder="Username" />';
        echo '<input name="password" type="text" placeholder="Password" />';
        echo '<button type="submit">Submit</button>';
        echo '</form>';
    }

    if (!$token = $client->getAccessToken()) {
        echo 'No access token<br />';
    } elseif ($token->hasExpired()) {
        echo 'Access token expired<br />';
    } else {
        echo 'Access token: <pre>' . htmlentities(print_r($token, true)) . '</pre><br />';
        echo 'Access token: <pre>' . htmlentities(json_encode($token, JSON_PRETTY_PRINT)) . '</pre><br />';
        echo 'Expires in: ' . htmlentities(print_r($token->getExpiresIn(), true)) . '<br />';

        try {
            if (method_exists($client, 'getTokenInfo')) {
                $token_info = $client->getTokenInfo();

                echo 'Access token info: <pre>' . htmlentities(print_r($token_info, true)) . '</pre><br />';
            }

            if ($client instanceof UserProfilesInterface) {
                $user = $client->getUserProfile();

                echo 'User profile: <pre>' . htmlentities(print_r($user, true)) . '</pre><br />';
            }

            if ($client instanceof UserPicturesInterface) {
                $picture_url = $client->getUserPictureUrl(60);

                echo 'User picture URL: <pre>' . htmlentities(print_r($picture_url, true)) . '</pre><br />';

                echo '<img src="' . htmlentities($picture_url) . '" /><br />';
            }
        } catch (GuzzleHttp\Exception\ClientException $exception) {
            echo 'Error: <pre>' . htmlentities(print_r($exception, true)) . '</pre><br />';
            echo 'Response: <pre>' . htmlentities(print_r($exception->getResponse()->getBody()->__toString(), true)) . '</pre><br />';
        } catch (Exception $exception) {
            echo 'Error: <pre>' . htmlentities(print_r($exception, true)) . '</pre><br />';
        }
    }

    if ($client instanceof Discord) {
        echo 'CREATE_INSTANT_INVITE, KICK_MEMBERS and ADMINISTRATOR permissions integer: <pre>' . htmlentities(print_r($client->permissionsToInteger([
            'CREATE_INSTANT_INVITE',
            'KICK_MEMBERS',
            'ADMINISTRATOR',
        ]), true)) . '</pre><br />';

        echo 'Permissions integer `11` permissions: <pre>' . htmlentities(print_r($client->integerToPermissions(11), true)) . '</pre><br />';

        echo 'Permissions integer `11` has `ADMINISTRATOR` permission: <pre>' . htmlentities(print_r($client->integerHasPermission(11, 'ADMINISTRATOR'), true)) . '</pre><br />';

        echo '<a href="' . htmlentities($client->inviteBot(['ADMINISTRATOR'], $client_info['redirect_url'], $client_info['scope'])) . '">Invite bot with administrator permissions</a><br />';

        echo '<a href="' . htmlentities($client->inviteWebhook($client_info['redirect_url'], $client_info['scope'])) . '">Invite webhook</a><br />';
    }
}

echo 'Session: <pre>' . htmlentities(print_r($_SESSION, true)) . '</pre><br />';
