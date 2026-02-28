<?php
return [
    'app' => [
        'name'    => 'AI UI Panel',
        'env'     => 'production', // production | development
        'debug'   => false,
        'url'     => 'https://aiui.telpass.io',
        'secret'  => 'CHANGE_THIS_TO_A_RANDOM_32_CHAR_STRING',
        'timezone' => 'Europe/Istanbul',
    ],

    'local_db' => [
        'driver'    => 'mysql',
        'host'      => '127.0.0.1',
        'port'      => 3306,
        'database'  => 'aiui_local',
        'username'  => 'aiui_user',
        'password'  => 'CHANGE_THIS_PASSWORD',
        'charset'   => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix'    => '',
    ],

    'redis' => [
        'host'     => '127.0.0.1',
        'port'     => 6379,
        'password' => null,
        'database' => 0,
    ],

    'session' => [
        'lifetime' => 480, // dakika
        'name'     => 'aiui_session',
    ],

    'upload' => [
        'path'     => __DIR__ . '/../uploads',
        'max_size' => 104857600, // 100MB
        'allowed'  => ['txt', 'srt'],
    ],
];
