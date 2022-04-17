<?php

namespace App\Http\Middleware;

use Framework\Http\Actions\PageNotFound;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\Diactoros\Response\HtmlResponse;

class AuthMiddleware implements MiddlewareInterface
{

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // TODO: Implement process() method.

        if (0) {
            return new Response("bad", 403);
        }

        $response = $handler->handle($request);

        if($response instanceof PageNotFound) {
            return new Response("catched)");
        }

        return $response;
    }
}
