<?php

namespace Framework\Http\Actions;


use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\Diactoros\Response\HtmlResponse;

class PageNotFound implements RequestHandlerInterface
{
    public function handle(RequestInterface $request): ResponseInterface
    {
        return new Response\JsonResponse(['status' => false, 'errors' => ['not found']], 404);
    }
}