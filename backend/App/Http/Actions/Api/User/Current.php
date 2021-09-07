<?php

namespace App\Http\Actions\Api\User;


use App\Http\Interfaces\Action;
use App\Http\Resources\User\UserResource;
use App\Models\User;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\ServerRequest;

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
            'user' => (new UserResource($user))->toArray(),
            'permissions' => [],
        ]);
    }
}
