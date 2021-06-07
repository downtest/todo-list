<?php


namespace App\Http\Actions\Api\Tasks;


use App\Http\BusinessServices\TasksInMongo;
use App\Http\Interfaces\Action;
use App\Models\User;
use Framework\Services\DBMongo;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Update extends Action
{
    public function validationRules(ServerRequestInterface $request): array
    {
        return [
            'collectionId' => ['nullable', 'string'],
            'taskId' => ['required', 'string'],
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

        if (!$task = $db->findById($collectionName, $request->getAttribute('taskId'))) {
            return $this->errorResponse(["Не найдена таска {$request->getAttribute('taskId')}"]);
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

        if (!$db->updateOne($collectionName, $request->getAttributes())) {
            return $this->errorResponse(['Не удалось обновить запись']);
        }

        return $this->successResponse();
    }
}
