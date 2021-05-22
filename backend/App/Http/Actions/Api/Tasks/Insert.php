<?php


namespace App\Http\Actions\Api\Tasks;


use App\Http\Interfaces\Action;
use App\Models\User;
use Framework\Services\Config;
use Framework\Services\DBMongo;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response\JsonResponse;

class Insert extends Action
{

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $db = DBMongo::getInstance();
        $collectionName = 'tasks'.User::current()['id'];

        print_r($db);
        exit();

        $result = $db->$collectionName->insertOne(['text' => $request->getAttribute('text')]);

        return new JsonResponse([
            'newTaskId' => $result->getInsertedIds(),
        ]);
    }
}
