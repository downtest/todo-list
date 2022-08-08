<?php

return [
    'id' => [
        'type' => 'int',
        'primary' => true,
        'ordinal_position' => 1,
        'autoincrement' => true,
        'is_nullable' => false,
    ],
    'user_id' => [
        'type' => 'int',
        'ordinal_position' => 2,
        'is_nullable' => false,
        'indexes' => [
            'user_firebase_tokens_user_id_index' => [
                'unique' => false,
                'columns' => [
                    'user_id'
                ],
            ],
        ],
    ],
    'token' => [
        'type' => 'varchar',
        'ordinal_position' => 3,
        'is_nullable' => true,
        'max_length' => 200,
        'indexes' => [
            'user_firebase_tokens_token_index' => [
                'unique' => true,
                'columns' => [
                    'token',
                    'device_hash',
                ],
            ],
        ],
    ],
    'device_hash' => [
        'type' => 'varchar',
        'ordinal_position' => 4,
        'is_nullable' => true,
        'max_length' => 255,
        'indexes' => [
            'user_firebase_tokens_user_id_device_hash_index' => [
                'unique' => true,
                'columns' => [
                    'user_id',
                    'device_hash',
                ],
            ],
        ],
    ],
    'device' => [
        'type' => 'varchar',
        'ordinal_position' => 5,
        'is_nullable' => true,
        'max_length' => 255,
    ],
];
