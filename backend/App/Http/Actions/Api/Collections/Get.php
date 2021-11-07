<?php

namespace App\Http\Actions\Api\Collections;

use App\Http\Interfaces\Action;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Get extends Action
{
    public function validationRules($request): array
    {
        return [];
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $collections = [
            ['id' => '1', 'name' => 'Личное'],
            ['id' => '2', 'name' => 'Рабочее'],
            ['id' => '546', 'name' => 'Нас пригласили другие'],
        ];

        return $this->successResponse([
            'status' => true,
            'collections' => $collections,
        ]);
    }
}