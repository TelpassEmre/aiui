<?php
// Bu dosya runtime'da db_connections tablosundan dinamik olarak doldurulur.
// Sadece fallback / default bağlantıları içerir.

return [
    'default' => 'local',

    'connections' => [
        'local' => [
            'driver'    => 'mysql',
            'host'      => '127.0.0.1',
            'port'      => 3306,
            'database'  => 'aiui_local',
            'username'  => 'aiui_user',
            'password'  => '',
            'charset'   => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix'    => '',
        ],

        // Remote bağlantılar db_connections tablosundan dinamik yüklenir.
        // Örnek statik tanım:
        'aivoice' => [
            'driver'    => 'mysql',
            'host'      => '5.189.186.115',
            'port'      => 3306,
            'database'  => 'aivoice',
            'username'  => 'scriptuser',
            'password'  => 'scriptuser',
            'charset'   => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix'    => '',
        ],

        'application' => [
            'driver'    => 'mysql',
            'host'      => '5.189.186.115',
            'port'      => 3306,
            'database'  => 'application',
            'username'  => 'scriptuser',
            'password'  => 'scriptuser',
            'charset'   => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix'    => '',
        ],
    ],
];
