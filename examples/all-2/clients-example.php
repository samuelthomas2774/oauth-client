<?php

/**
 * Copy this to clients.php and add your client IDs and secrets.
 */

return [
    'speechmore' => [
        'name' => 'SpeechMore',
        'class' => \OAuth2\Providers\SpeechMore\SpeechMore::class,
        'id' => '...',
        'secret' => '...',
        'redirect_url' => 'https://' . $_SERVER['HTTP_HOST'] . '/oauth-client/examples/all-2/code.php/speechmore',
        'scope' => [
            'user:name',
            'user:avatar',
        ],
        'options' => [
            'session_prefix' => 'speechmore_',
        ],
    ],
];
