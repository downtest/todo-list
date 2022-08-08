<?php

use Framework\Http\ExceptionsHandler as FrameworkExceptionHandler;

set_error_handler("error_handler", E_ALL);

// Обработка всех ошибок
function error_handler($errno, $errstr, $file, $line) {
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
        $exceptionHandler = (new \App\Http\ExceptionsHandler())->handle(new Exception("$errType: $errstr IN FILE $file ON LINE $line"));
    } else {
        $exceptionHandler = (new FrameworkExceptionHandler())->handle(new Exception("$errType: $errstr IN FILE $file ON LINE $line"));
    }

    // Браузер почему-то не показывает тело, если статус не 200
//    http_response_code($exceptionHandler->getStatusCode());

    exit($exceptionHandler->getBody());
}

require './app.php';
