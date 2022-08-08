<?php

namespace Framework\Http\Actions;


use Framework\Http\Responses\JsonResponse;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;

class PageNotFound implements RequestHandlerInterface
{
    public function handle(RequestInterface $request): ResponseInterface
    {
        return new JsonResponse(['status' => false, 'errors' => ['URL not found']], 404);
    }
}