<?php


namespace App\Http\Actions\Api\Nodes;


use App\Services\TasksInMongo;
use App\Http\Interfaces\Action;
use App\Models\User;
use Framework\Services\DBMongo;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Delete extends Action
{

    public function validationRules(ServerRequestInterface $request): array
    {
        return [
            'collectionId' => ['nullable', 'string'],
            'taskId' => ['nullable', 'string'],
        ];
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $db = DBMongo::getInstance();
        $collectionName = User::getDefaultCollectionForCurrentUser();

        if (!$task = $db->findById($collectionName, $request->getAttribute('taskId'))) {
            return $this->errorResponse(["Не найдена таска {$request->getAttribute('taskId')}"]);
        }

        $tasksToDelete = TasksInMongo::getInstance()->collectChildren($collectionName, $request->getAttribute('taskId'));

        $deletedCnt = TasksInMongo::getInstance()->deleteByIds($collectionName, $tasksToDelete);

        return $this->successResponse([
            'deletedTasks' => $tasksToDelete,
            'deletedCnt' => $deletedCnt,
        ]);
    }
}
