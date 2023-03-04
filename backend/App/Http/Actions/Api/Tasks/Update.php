<?php


namespace App\Http\Actions\Api\Tasks;


use App\Services\TasksInMongo;
use App\Http\Interfaces\Action;
use App\Models\User;
use Framework\Services\DBMongo;
use Framework\Tools\Arr;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Laminas\Diactoros\Response\JsonResponse;

class Update extends Action
{
    /**
     * Првоерка валидации проходит в \App\Http\Interfaces\Action::getValidationErrors()
     *
     * @param ServerRequestInterface $request
     * @return string[] Массив ошибок
     */
    public function validationRules(ServerRequestInterface $request): array
    {
        return [
            'collectionId' => ['nullable', 'string'],
            'task.id' => ['required', 'string'],
            'task.index' => ['nullable', 'number'],
            'task.parentId' => ['nullable', 'string'],
            'task.message' => ['nullable', 'string'],
            'task.labels' => ['nullable', 'array'],
        ];
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        /** @var DBMongo $db */
        $db = DBMongo::getInstance();
        $collectionName = User::getDefaultCollectionForCurrentUser();
        $taskInput = $request->getAttribute('task');

        try {
            TasksInMongo::getInstance()->update($collectionName, $taskInput);
        } catch (\Throwable $exception) {
            return $this->errorResponse(['Не удалось обновить запись: '.$exception->getMessage()]);
        }

        return new JsonResponse([
            'indexes' => $db->find($collectionName,[], ['projection' => ['index' => 1]]),
            'updatedItem' => $db->findById($collectionName, $taskInput['id']),
        ]);
    }
}
