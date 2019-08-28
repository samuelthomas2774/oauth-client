<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use OAuth2\Providers\SpeechMore;

// Start a session
// This must be done after loading the autoloader (so access tokens can be unserialized properly) and before the client is created so the access token can be restored from the session
session_start();

$client_info = require __DIR__ . '/client.php';
$client = new SpeechMore($client_info['id'], $client_info['secret']);

try {
    // Validate $_GET['state'], get an access token from $_GET['code'] and save it to the session
    $token = $client->getAccessTokenFromRequestCode('https://' . $_SERVER['HTTP_HOST'] . '/oauth-client/examples/speechmore/code.php', $client_info['scope']);

    echo 'Success!<br />';
    echo 'Code: ' . htmlentities($_GET['code']) . '<br />';
    echo 'Access token: ' . htmlentities(print_r($token, true)) . '<br />';
} catch (Exception $exception) {
    echo 'Error: ' . $exception->getMessage() . '<br />';
}

echo '<a href=".">Home</a>';
