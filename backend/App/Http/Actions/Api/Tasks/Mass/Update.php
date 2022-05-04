<?php


namespace App\Http\Actions\Api\Tasks\Mass;


use App\Http\BusinessServices\TasksInMongo;
use App\Http\Interfaces\Action;
use App\Models\User;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Update extends Action
{
    public function validationRules(ServerRequestInterface $request): array
    {
        return [
            'tasks' => ['required', 'array'],
        ];
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $collectionName = User::getDefaultCollectionForCurrentUser();

        try {
            $result = TasksInMongo::getInstance()->massUpdateOrCreate($collectionName, $request->getAttribute('tasks'));
        } catch (\Throwable $exception) {
            return $this->errorResponse(["{$exception->getMessage()} IN {$exception->getFile()} ON LINE {$exception->getLine()}"]);
        }

        return $this->successResponse(['tasks' => $result]);
    }
}
