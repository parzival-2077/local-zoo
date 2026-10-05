<?php

return [
    'paths' => [
        'migrations' => __DIR__ . '/db/migrations',
        'seeds'      => __DIR__ . '/db/seeds',
    ],
    'environments' => [
        'default_migration_table' => 'phinxlog',
        'default_environment'     => 'development',
        'development' => [
            'adapter' => 'pgsql',
            'host'    => getenv('DB_HOST') ?: 'postgres',
            'name'    => getenv('DB_NAME') ?: 'app',
            'user'    => getenv('DB_USER') ?: 'app',
            'pass'    => getenv('DB_PASSWORD') ?: '',
            'port'    => getenv('DB_PORT') ?: '5432',
            'charset' => 'utf8',
        ],
    ],
    'version_order' => 'creation',
];