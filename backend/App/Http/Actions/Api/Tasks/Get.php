<?php


namespace App\Http\Actions\Api\Tasks;


use App\Http\Interfaces\Action;
use App\Models\User;
use Framework\Services\DBMongo;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\JsonResponse;

class Get extends Action
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $db = DBMongo::getInstance();
        $collectionName = 'tasks'.User::current()['id'];
        $collection = $db->find($collectionName, []);

        return new JsonResponse($collection);
    }
}
