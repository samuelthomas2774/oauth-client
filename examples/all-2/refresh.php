<?php

ini_set('display_errors', true);

require_once __DIR__ . '/../../vendor/autoload.php';

// Start a session
// This must be done after loading the autoloader (so access tokens can be unserialized properly) and before the client
// is created so the access token can be restored from the session
session_start();

$client_info = require __DIR__ . '/client.php';

if ($client_info) {
    $client = new $client_info['class']($client_info['id'], $client_info['secret'], null, $client_info['options']);

    try {
        $old_token = $client->getAccessToken();

        if (isset($_GET['refresh_token'])) {
            $refresh_token = $_GET['refresh_token'];
            $token = $client->getAccessTokenFromRefreshToken($_GET['refresh_token']);
        } else {
            // Refresh the current access token
            $refresh_token = $old_token ? $old_token->getRefreshToken() : null;
            $token = $client->refreshAccessToken();
        }

        echo 'Success!<br />';

        echo 'Refresh token: <pre>' . htmlentities($refresh_token) . '</pre><br />';
        echo 'Access token: <pre>' . htmlentities(print_r($token, true)) . '</pre><br />';
        echo 'Old access token: <pre>' . htmlentities(print_r($old_token, true)) . '</pre><br />';
    } catch (Exception $exception) {
        echo 'Error: ' . htmlentities($exception->getMessage()) . '<br />';
    } catch (TypeError $exception) {
        echo 'Error: ' . htmlentities($exception->getMessage()) . '<br />';
    }
}

echo '<a href="' . htmlentities((substr_count($_SERVER['SCRIPT_NAME'], '/', 1) >= 1 ? dirname($_SERVER['SCRIPT_NAME']) :
    '') . '/index.php' . $_SERVER['PATH_INFO']) . '">Home</a><br />';
