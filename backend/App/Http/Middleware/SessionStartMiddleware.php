<?php

namespace App\Http\Middleware;

use Exception;
use Framework\Services\Session;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class SessionStartMiddleware implements MiddlewareInterface
{

    /**
     * @throws Exception
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        session_start();

        Session::fromGlobal();

        return $handler->handle($request);
    }
}
