<?php

return [
    'sql' => [
        'default' => 'main',
        'main' => [
            'driver' => 'pgsql',
            'host' => getenv('POSTGRES_HOST') ?: '127.0.0.1',
            'port' => getenv('POSTGRES_PORT') ?: '3306',
            'database' => getenv('POSTGRES_DB') ?: 'forge',
            'username' => getenv('POSTGRES_USER') ?: 'forge',
            'password' => getenv('POSTGRES_PASSWORD') ?: '',
            'unix_socket' => getenv('DB_SOCKET') ?: '',
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => getenv('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],
    ],
    'mongo' => [
        'username' => getenv('MONGO_USER') ?: 'forge',
        'password' => getenv('MONGO_PASSWORD') ?: '',
        'host' => getenv('MONGO_HOST') ?: 'mongo',
        'port' => getenv('MONGO_PORT') ?: '27017',
        'database' => getenv('MONGO_DATABASE') ?: '',
    ],
];
