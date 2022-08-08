<?php

namespace App\Http\Middleware;

use Exception;
use Framework\Services\Headers;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class HeadersLoadMiddleware implements MiddlewareInterface
{

    /**
     * @throws Exception
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        Headers::fromGlobal();

        return $handler->handle($request);
    }
}
