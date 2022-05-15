<?php

return [
    'vk' => [
        'client_id' => '8156687',
        'client_secret' => 'rcrbQblNRMkufuozhxSQ',
        'redirect_uri' => getenv('EXTERNAL_OAUTH_FULL_URL') ?: 'https://listodo.ru',
    ],
];
