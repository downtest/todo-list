<?php

namespace App\Http\Middleware;

use App\Http\ExceptionsHandler;
use Framework\Http\ExceptionsHandler as FrameworkExceptionHandler;
use Laminas\Diactoros\Response\HtmlResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;

class ErrorHandlerMiddleware implements MiddlewareInterface
{

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch (Throwable $exception) {
            if (class_exists('\App\Http\ExceptionsHandler')) {
                $exceptionHandler = (new ExceptionsHandler($request))->handle($exception);
            } else {
                $exceptionHandler = (new FrameworkExceptionHandler($request))->handle($exception);
            }

            http_response_code($exceptionHandler->getStatusCode());

            return new HtmlResponse($exceptionHandler->getBody());
        }
    }
}
