<?php

return [
    'users' => [
        'id' => [
            'type' => 'bigint', // 	1 .. 9223372036854775807
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
        'password' => [
            'type' => 'varchar',
            'is_nullable' => true,
            'max_length' => '200',
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
        'created_at' => [
            'column_default' => 'CURRENT_TIMESTAMP',
            'is_nullable' => true,
            'type' => 'date',
            'max_length' => null,
        ],
    ],
    'collections' => [
        'id' => [
            'type' => 'string',
            'primary' => true,
            'ordinal_position' => 1,
            'autoincrement' => false,
            'is_nullable' => false,
            'max_length' => 100,
        ],
        'owner_id' => [
            'ordinal_position' => 2,
            'column_default' => null,
            'is_nullable' => false,
            'type' => 'bigint',
            'max_length' => 12,
            'indexes' => [
                'collections_owner_id' => [
                    'unique' => false,
                    'columns' => [
                        'owner_id'
                    ],
                ],
            ],
        ],
        'name' => [
            'ordinal_position' => 3,
            'column_default' => null,
            'is_nullable' => true,
            'type' => 'varchar',
            'max_length' => null,
        ],
        'is_own' => [
            'ordinal_position' => 4,
            'column_default' => 'false', // там будет преобразование в строку
            'is_nullable' => false,
            'type' => 'boolean',
            'max_length' => null,
        ],
        'created_at' => [
            'ordinal_position' => 5,
            'column_default' => 'CURRENT_TIMESTAMP',
            'is_nullable' => true,
            'type' => 'date',
            'max_length' => null,
        ],
    ],
    'user_collection' => [
        'id' => [
            'type' => 'bigint',
            'primary' => true,
            'ordinal_position' => 1,
            'autoincrement' => true,
            'is_nullable' => false,
            'max_length' => 100,
        ],
        'user_id' => [
            'ordinal_position' => 2,
            'column_default' => null,
            'autoincrement' => false,
            'is_nullable' => false,
            'type' => 'bigint',
            'max_length' => 100,
            'indexes' => [
                'collections_user_id' => [
                    'unique' => false,
                    'columns' => [
                        'user_id',
                    ],
                ],
            ],
            'foreign' => [
                'name' => 'foreign_user_collection_user_id',
                'foreign_table' => 'users',
                'foreign_column' => 'id',
                'on_update' => 'CASCADE',
                'on_delete' => 'CASCADE',
            ],
        ],
        'collection_id' => [
            'ordinal_position' => 3,
            'column_default' => null,
            'is_nullable' => false,
            'type' => 'string',
            'max_length' => 100,
            'indexes' => [
                'collections_collection_id' => [
                    'unique' => false,
                    'columns' => [
                        'collection_id',
                    ],
                ],
            ],
            'foreign' => [
                'name' => 'foreign_user_collection_collection_id',
                'foreign_table' => 'collections',
                'foreign_column' => 'id',
                'on_update' => 'CASCADE',
                'on_delete' => 'CASCADE',
            ],
        ],
        'create_access' => [
            'ordinal_position' => 4,
            'column_default' => 'false',
            'is_nullable' => false,
            'type' => 'bool',
            'max_length' => null,
        ],
        'update_access' => [
            'ordinal_position' => 5,
            'column_default' => 'false',
            'is_nullable' => false,
            'type' => 'bool',
            'max_length' => null,
        ],
        'delete_access' => [
            'ordinal_position' => 6,
            'column_default' => 'false',
            'is_nullable' => false,
            'type' => 'bool',
            'max_length' => null,
        ],
        'created_at' => [
            'ordinal_position' => 7,
            'column_default' => 'CURRENT_TIMESTAMP',
            'is_nullable' => true,
            'type' => 'date',
            'max_length' => null,
        ],
    ],
    'user_tokens' => [
        'token' => [
            'ordinal_position' => 1,
            'type' => 'string',
            'primary' => true,
            'autoincrement' => false,
            'is_nullable' => false,
            'max_length' => 100,
        ],
        'user_id' => [
            'ordinal_position' => 2,
            'type' => 'bigint',
            'is_nullable' => false,
            'max_length' => 12,
            'indexes' => [
                'user_tokens_user_id' => [
                    'unique' => false,
                    'columns' => [
                        'user_id'
                    ],
                ],
            ],
        ],
        'device_header' => [
            'ordinal_position' => 3,
            'type' => 'varchar',
            'is_nullable' => true,
            'max_length' => 255,
            'comment' => 'Заголовок устройства',
        ],
        'created_at' => [
            'ordinal_position' => 4,
            'column_default' => 'CURRENT_TIMESTAMP',
            'is_nullable' => true,
            'type' => 'timestamp',
            'max_length' => null,
        ],
        'expire_at' => [
            'ordinal_position' => 5,
            'column_default' => null,
            'is_nullable' => true,
            'type' => 'timestamp',
            'max_length' => null,
        ],
    ],
    'user_contacts' => [
        'id' => [
            'type' => 'bigint',
            'primary' => true,
            'ordinal_position' => 1,
            'autoincrement' => true,
            'is_nullable' => false,
        ],
        'contact_id' => [
            'type' => 'int',
            'ordinal_position' => 2,
            'is_nullable' => true,
            'indexes' => [
                'user_contacts_contact_id' => [
                    'unique' => false,
                    'columns' => [
                        'contact_id'
                    ],
                ],
                'user_contacts_contact_id_user_id_value_index' => [
                    'unique' => true,
                    'columns' => [
                        'contact_id',
                        'user_id',
                        'value',
                    ],
                ],
            ],
            'foreign' => [
                'name' => 'foreign_user_contacts_contact_id',
                'foreign_table' => 'lib_contacts',
                'foreign_column' => 'id',
                'on_update' => 'CASCADE',
                'on_delete' => 'CASCADE',
            ],
        ],
        'user_id' => [
            'type' => 'int',
            'ordinal_position' => 3,
            'is_nullable' => true,
            'indexes' => [
                'user_contacts_user_id' => [
                    'unique' => false,
                    'columns' => [
                        'user_id'
                    ],
                ],
            ],
            'foreign' => [
                'name' => 'foreign_user_contacts_user_id',
                'foreign_table' => 'users',
                'foreign_column' => 'id',
                'on_update' => 'CASCADE',
                'on_delete' => 'CASCADE',
            ],
        ],
        'value' => [
            'type' => 'varchar',
            'ordinal_position' => 4,
            'is_nullable' => true,
            'max_length' => 255,
        ],
    ],
    'user_firebase_tokens' => require 'default/user_firebase_tokens.php',
    'lib_contacts' => [
        'id' => [
            'type' => 'int',
            'primary' => true,
            'ordinal_position' => 1,
            'autoincrement' => true,
            'is_nullable' => false,
        ],
        'name' => [
            'type' => 'varchar',
            'ordinal_position' => 2,
            'is_nullable' => false,
            'max_length' => 100,
            'indexes' => [
                'lib_contacts_name_index' => [
                    'unique' => true,
                    'columns' => [
                        'name'
                    ],
                ],
            ],
        ],
        'title' => [
            'type' => 'varchar',
            'ordinal_position' => 3,
            'is_nullable' => false,
            'max_length' => 100,
        ],
    ],

];
