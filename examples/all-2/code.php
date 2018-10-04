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
        // Validate $_GET['state'], get an access token from $_GET['code'] and save it to the session
        $token = $client->getAccessTokenFromRequestCode($client_info['redirect_url'], $client_info['scope']);

        $state = $client->getRequestState();

        echo 'Success!<br />';

        if (isset($state->link)) {
            echo 'You clicked link #' . htmlentities($state->link) . '<br />';
        }

        echo 'Code: <pre>' . htmlentities($_GET['code']) . '</pre><br />';
        echo 'State: <pre>' . htmlentities(print_r($state, true)) . '</pre><br />';
        echo 'Access token: <pre>' . htmlentities(print_r($token, true)) . '</pre><br />';
    } catch (Exception $exception) {
        echo 'Error: ' . htmlentities($exception->getMessage()) . '<br />';
    }
}

echo '<a href="..">Home</a>';
