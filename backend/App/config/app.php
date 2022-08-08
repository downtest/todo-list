<?php

return [
    'host' => getenv('FRONTEND_HOST') ?: 'http://localhost:81',
    'default_lang' => 'en',
    'environment' => getenv('ENVIRONMENT') ?? 'temp',
];
