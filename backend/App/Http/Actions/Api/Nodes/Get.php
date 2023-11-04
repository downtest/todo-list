<?php


namespace App\Http\Actions\Api\Nodes;


use App\Http\Interfaces\Action;
use App\Http\Resources\Tasks\TaskResource;
use App\Models\User;
use Framework\Services\DBMongo;
use Framework\Services\Lang;
use Framework\Services\Mailer;
use Framework\Services\Templater;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Laminas\Diactoros\Response\JsonResponse;

class Get extends Action
{
    public function validationRules(ServerRequestInterface $request): array
    {
        return [
            'collectionId' => ['nullable', 'string'],
        ];
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if (!User::current()) {
            return $this->errorResponse(['Unauthorised'], 403);
        }

        $db = DBMongo::getInstance();
        $collectionName = User::getDefaultCollectionForCurrentUser();
        $collection = $db->find($collectionName, []);

        return new JsonResponse(TaskResource::collection($collection));
    }
}
