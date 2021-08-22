<?php


namespace App\Http\Actions\Api\Tasks;


use App\Http\BusinessServices\TasksInMongo;
use App\Http\Interfaces\Action;
use App\Models\User;
use Framework\Services\DBMongo;
use Framework\Tools\Arr;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response\JsonResponse;

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
        $collectionName = 'tasks'.User::current()['id'];

        TasksInMongo::getInstance()->addToParent(
            $collectionName,
            $request->getAttribute('parentId'),
            $request->getAttribute('index')
        );

        $maxIndex = TasksInMongo::getInstance()->getMaxId($collectionName, $request->getAttribute('parentId') ?? null);

        $newId = $db->insertOne($collectionName, Arr::except($request->getAttributes(), ['isNew', 'id', 'confirmed', 'isNew']));

        return new JsonResponse($db->findById($collectionName, $newId));
    }
}
