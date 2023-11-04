<?php


namespace App\Http\Actions\Api\Nodes;


use App\Services\TasksInMongo;
use App\Http\Interfaces\Action;
use App\Models\User;
use Framework\Services\DBMongo;
use Framework\Tools\Arr;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\Diactoros\Response\JsonResponse;

class Insert extends Action
{

    public function validationRules(ServerRequestInterface $request): array
    {
        return [
            'collectionId' => ['nullable', 'string'],
            'parentId' => ['nullable', 'string'],
            'index' => ['nullable', 'number'],
            'message' => ['nullable', 'string'],
        ];
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $db = DBMongo::getInstance();
        $collectionName = User::getDefaultCollectionForCurrentUser();

        $newId = TasksInMongo::getInstance()->create($collectionName, $request->getAttributes());

        return new JsonResponse($db->findById($collectionName, $newId));
    }
}
