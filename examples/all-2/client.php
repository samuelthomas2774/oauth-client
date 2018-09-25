<?php

$clients = require __DIR__ . '/clients.php';

if (!isset($_SERVER['PATH_INFO'])) {
    echo 'No server.<br />';

    foreach ($clients as $url => $client) {
        echo '<a href="' . $_SERVER['PHP_SELF'] . '/' . $url . '">' . $client['name'] . '</a><br />';
    }

    return;
}

$url = ltrim($_SERVER['PATH_INFO'], '/');

if (!isset($clients[$url])) {
    echo 'Unknown client "' . htmlentities($client_url) . '".<br />';
    return;
}

return $clients[$url];
