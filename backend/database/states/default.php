<?php

return [
    'users' => [
        'id' => [
            'type' => 'integer',
            'primary' => true,
            'autoincrement' => true,
        ],
        'lang' => [
            'comment' => 'Язык пользователя (ru, en...). Должен совпадать с названием папки в resources/localization',
            'type' => 'varchar',
            'is_nullable' => true,
            'max_length' => '5',
        ],
        'name' => [
            'type' => 'varchar',
            'is_nullable' => true,
            'max_length' => '200',
        ],
        'email' => [
            'type' => 'varchar',
            'is_nullable' => true,
            'max_length' => '200',
        ],
        'password' => [
            'type' => 'varchar',
            'is_nullable' => true,
            'max_length' => '200',
        ],
        'phone' => [
            'type' => 'varchar',
            'is_nullable' => true,
            'max_length' => '18',
        ],
        'password_change_requested_at' => [
            'comment' => 'Время в которое запросили смену пароля',
            'type' => 'timestamp',
            'is_nullable' => true,
            'max_length' => '64',
        ],
        'password_change_hash' => [
            'comment' => 'Хэш для сброса пароля',
            'type' => 'varchar',
            'is_nullable' => true,
            'max_length' => '64',
        ],
    ],
    'look_at_me' => [
        'id' => [
            'type' => 'integer',
            'primary' => true,
            'ordinal_position' => 1,
            'autoincrement' => true,
            'is_nullable' => false,
            'max_length' => null,
        ],
        'name' => [
            'ordinal_position' => 2,
            'column_default' => null,
            'is_nullable' => false,
            'type' => 'character varying',
            'max_length' => 12,
            'indexes' => [
                'look_at_me_name' => [
                    'unique' => false,
                    'columns' => [
                        'name'
                    ],
                ],
            ],
        ],
        'description' => [
            'ordinal_position' => 3,
            'column_default' => null,
            'is_nullable' => true,
            'type' => 'varchar',
            'max_length' => null,
            'indexes' => [
                'look_at_me_description' => [
                    'columns' => ['description'],
                ],
            ],
        ],
        'created_at' => [
            'ordinal_position' => 4,
            'column_default' => false,
            'is_nullable' => true,
            'type' => 'date',
            'max_length' => null,
        ],
    ],
];
