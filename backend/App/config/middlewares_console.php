<?php

return [
    '*' => [
        \App\Http\Middleware\ErrorHandlerMiddleware::class,
        \App\Http\Middleware\SessionStartMiddleware::class,
        \App\Http\Middleware\AuthMiddleware::class,
    ],
    '/api/*' => [\App\Http\Middleware\ApiMiddleware::class],
];
