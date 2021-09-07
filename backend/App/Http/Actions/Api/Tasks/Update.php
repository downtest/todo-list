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

        if (!$task = $db->findById($collectionName, $request->getAttribute('id'))) {
            return $this->errorResponse(["Не найдена таска {$request->getAttribute('id')}"]);
        }

        // Если изменён родитель, но не передан индекс в новом родителе
        if ($request->getAttribute('parentId') && !is_int($request->getAttribute('index'))) {
            $maxIndex = TasksInMongo::getInstance()->getMaxId($collectionName, $request->getAttribute('parentId'));

            $request = $request->withAttribute('index', $maxIndex);
        }

        if ($request->getAttribute('parentId') || $request->getAttribute('index')) {
            TasksInMongo::getInstance()->updateParent(
                $collectionName,
                $task['parentId'] ?? null,
                $request->getAttribute('parentId'),
                $task['index'] ?? 0,
                $request->getAttribute('index')
            );
        }

        try {
            $db->updateOne($collectionName, Arr::except($request->getAttributes(), ['collectionId', 'isNew', 'confirmed']));
        } catch (\Throwable $exception) {
            return $this->errorResponse(['Не удалось обновить запись']);
        }

        return new JsonResponse($db->findById($collectionName, $request->getAttribute('id')));
    }
}
