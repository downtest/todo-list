<?php

use Framework\Http\ExceptionsHandler as FrameworkExceptionHandler;

set_error_handler("error_handler", E_ALL);

function error_handler($errno, $errstr) {
    switch ($errno) {
        case E_NOTICE:
            $errType = 'Notice'; break;
        case E_WARNING:
            $errType = 'Warning'; break;
        case E_PARSE:
            $errType = 'Parse error'; break;
        default:
            $errType = $errno;
    }

    if (class_exists('\App\Http\ExceptionsHandler')) {
        $exceptionHandler = (new \App\Http\ExceptionsHandler())->handle(new Exception("$errType: $errstr"));
    } else {
        $exceptionHandler = (new FrameworkExceptionHandler())->handle(new Exception("$errType: $errstr"));
    }

    http_response_code($exceptionHandler->getStatusCode());

    exit($exceptionHandler->getBody());
}

require '../vendor/autoload.php';

$request = \Zend\Diactoros\ServerRequestFactory::fromGlobals();

(new \Framework\Http\Application(
    $request,
    new \Framework\Http\Middleware\PipelineHttp(),
    new \Framework\Http\Router\RouterHttp($request)
))->run();
