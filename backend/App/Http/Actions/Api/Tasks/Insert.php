<?php


namespace App\Http\Actions\Api\Tasks;


use App\Http\BusinessServices\TasksInMongo;
use App\Http\Interfaces\Action;
use App\Models\User;
use Framework\Services\DBMongo;
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

        $maxIndex = $db->find(
            $collectionName,
            ['parentId' => ['$eq' => $request->getAttribute('parentId')]],
            [
                //  Возвращаем только index
                'projection' => ['index' => 1],
                'sort' =>  ['index' => -1],
                'limit' => 1,
            ]
        )[0]['index'] ?? 0;

        $newId = $db->insertOne($collectionName, [
            'parentId' => $request->getAttribute('parentId'),
            'index' => $maxIndex + 1,
            'text' => $request->getAttribute('text'),
        ]);

        return new JsonResponse($db->findById($collectionName, $newId));
    }
}
