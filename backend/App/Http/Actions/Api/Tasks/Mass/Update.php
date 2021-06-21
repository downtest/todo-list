<?php


namespace App\Http\Actions\Api\Tasks\Mass;


use App\Http\Interfaces\Action;
use App\Http\Resources\Tasks\TaskResource;
use App\Models\User;
use Framework\Services\DBMongo;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\ServerRequest;

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
        $db = DBMongo::getInstance();
        $collectionName = 'tasks'.User::current()['id'];
        $insertAction = new \App\Http\Actions\Api\Tasks\Insert();
        $updateAction = new \App\Http\Actions\Api\Tasks\Update();
        $result = [];

        foreach ($request->getAttribute('tasks') as $task) {
            $oneTaskRequest = new ServerRequest();
            foreach ($task as $param => $value) {
                $oneTaskRequest = $oneTaskRequest->withAttribute($param, $value);
            }

            if ($task['isNew']) {
                // Нужно создать
                if ($errors = $insertAction->getValidationErrors($oneTaskRequest)) {
                    return $this->validationErrorResponse($errors);
                }

                $result[] = ['oldId' => $oneTaskRequest->getAttribute('id')] + (new TaskResource($insertAction->handle($oneTaskRequest)->getPayload()))->toArray();
            } else {
                // Нужно обновить
                if ($errors = $updateAction->getValidationErrors($oneTaskRequest)) {
                    return $this->validationErrorResponse($errors);
                }

                $result[] = ['oldId' => $oneTaskRequest->getAttribute('id')] + (new TaskResource($updateAction->handle($oneTaskRequest)->getPayload()))->toArray();
            }
        }

        return $this->successResponse(['tasks' => $result]);
    }
}
