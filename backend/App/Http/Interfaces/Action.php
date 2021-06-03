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
        return new JsonResponse(['success' => true, 'data' => $data]);
    }

    public function errorResponse(array $data = []): JsonResponse
    {
        return new JsonResponse(['success' => false, 'errors' => $data], 400);
    }

    public function validationErrorResponse(array $errors = []): JsonResponse
    {
        return new JsonResponse(['success' => false, 'errors' => $errors], 422);
    }
}
