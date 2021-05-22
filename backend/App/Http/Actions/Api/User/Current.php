<?php

namespace App\Http\Actions\Api\User;


use App\Http\Interfaces\Action;
use App\Models\User;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Diactoros\ServerRequest;

class Current extends Action
{
    /**
     * @param ServerRequest $request
     * @return ResponseInterface
     */
    public function handle(RequestInterface $request): ResponseInterface
    {
        $user = User::current();

        return new JsonResponse([
            'status' => true,
            'user' => $user,
            'permissions' => [],
        ]);
    }
}
