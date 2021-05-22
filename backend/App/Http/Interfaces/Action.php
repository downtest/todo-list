<?php


namespace App\Http\Interfaces;


use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response\JsonResponse;

abstract class Action implements RequestHandlerInterface
{

    abstract public function handle(ServerRequestInterface $request): ResponseInterface;

    public function validationRules(ServerRequestInterface $request): array
    {
        return [];
    }

    public function successResponse(array $data = []): JsonResponse
    {
        return new JsonResponse($data);
    }

    public function errorResponse(array $data = []): JsonResponse
    {
        return new JsonResponse($data, 400);
    }

    public function validationErrorResponse(array $errors = []): JsonResponse
    {
        return new JsonResponse($errors, 422);
    }
}
