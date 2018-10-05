<?php

ini_set('display_errors', true);

require_once __DIR__ . '/../../vendor/autoload.php';

// Start a session
// This must be done after loading the autoloader (so access tokens can be unserialized properly) and before the client is created so the access token can be restored from the session
session_start();

$client_info = require __DIR__ . '/client.php';

if ($client_info) {
    $client = new $client_info['class']($client_info['id'], $client_info['secret'], null, $client_info['options']);

    try {
        if (!isset($_POST['username']) || !isset($_POST['password'])) {
            throw new Exception('Missing username and password.');
        }

        $token = $client->getAccessTokenFromUserCredentials($_POST['username'], $_POST['password']);

        echo 'Success!<br />';

        echo 'Username: <pre>' . htmlentities($_POST['username']) . '</pre><br />';
        echo 'Access token: <pre>' . htmlentities(print_r($token, true)) . '</pre><br />';
    } catch (Exception $exception) {
        echo 'Error: ' . htmlentities($exception->getMessage()) . '<br />';
    }
}

echo '<a href="' . htmlentities(dirname($_SERVER['SCRIPT_NAME']) . '/index.php' . $_SERVER['PATH_INFO']) . '">Home</a><br />';
