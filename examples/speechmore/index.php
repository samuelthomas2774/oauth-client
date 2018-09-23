<?php

ini_set('display_errors', true);

require_once __DIR__ . '/../../vendor/autoload.php';

use OAuth2\Providers\SpeechMore;
use OAuth2\UserProfilesInterface;
use OAuth2\UserPicturesInterface;

// Start a session
// This must be done after loading the autoloader (so access tokens can be unserialized properly) and before the client is created so the access token can be restored from the session
session_start();

$client_info = require __DIR__ . '/client.php';
$client = new SpeechMore($client_info['id'], $client_info['secret']);

// Delete the access token if needed
if (isset($_GET['del_token'])) $client->setAccessToken(null);

// Output a link to the authorise endpoint
echo '<a href="' . htmlentities($client->generateAuthoriseUrlAndState('https://' . $_SERVER['HTTP_HOST'] . '/oauth-client/examples/speechmore/code.php', $client_info['scope'])) . '">Login to SpeechMore</a><br />';

if ($token = $client->getAccessToken()) {
    echo 'Access token: <pre>' . htmlentities(print_r($token, true)) . '</pre><br />';

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
} else {
    echo 'No access token<br />';
}

echo 'Session: <pre>' . htmlentities(print_r($_SESSION, true)) . '</pre><br />';
