<?php


namespace App\Http\Actions\Api\Tasks;


use App\Http\BusinessServices\TasksInMongo;
use App\Http\Interfaces\Action;
use App\Models\User;
use Framework\Services\DBMongo;
use Framework\Tools\Arr;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Laminas\Diactoros\Response\JsonResponse;

class Update extends Action
{
    public function validationRules(ServerRequestInterface $request): array
    {
        return [
            'collectionId' => ['nullable', 'string'],
            'id' => ['required', 'string'],
            'index' => ['nullable', 'number'],
            'parentId' => ['nullable', 'string'],
            'message' => ['nullable', 'string'],
            'labels' => ['nullable', 'array'],
        ];
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $db = DBMongo::getInstance();
        $collectionName = 'tasks'.User::current()['id'];

        try {
            TasksInMongo::getInstance()->update($collectionName, $request->getAttributes());
        } catch (\Throwable $exception) {
            return $this->errorResponse(['Не удалось обновить запись: '.$exception->getMessage()]);
        }

        return new JsonResponse($db->findById($collectionName, $request->getAttribute('id')));
    }
}
