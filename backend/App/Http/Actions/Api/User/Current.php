<?php

namespace App\Http\Actions\Api\User;


use App\Http\Interfaces\Action;
use App\Http\Resources\User\UserResource;
use App\Models\Collection;
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
        if ($user = User::current()) {
            $userCollections = Collection::query("SELECT * FROM ".Collection::$table." WHERE owner_id = {$user['id']} ORDER BY created_at");
        }

        return new JsonResponse([
            'status' => true,
            'user' => (new UserResource($user))->toArray(),
            'permissions' => [],
            'collections' => $userCollections ?? [],
//            'currentCollection' => array_filter($userCollections ?? [], fn ($collection) => $collection['is_own'])[0] ?? null,
        ]);
    }
}
