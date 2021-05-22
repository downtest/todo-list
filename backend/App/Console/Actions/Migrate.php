<?php

namespace App\Console\Actions;


use App\Console\Actions\Interfaces\BaseAction;
use Framework\Services\DBPostgres;
use Framework\Services\DBState;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;

class Migrate extends BaseAction
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $dbState = new DBState('postgres');
        $db = DBPostgres::getInstance();
        $response = '';

        $queries = $dbState->getQueries();

        foreach ($queries as $query) {
            try {
                $response .= PHP_EOL . "Executing $query... ";
                $result = $db->exec($query);
                $response .= print_r($result, true) . PHP_EOL;
            } catch (\Throwable $exception) {
                $response .= 'failed' . PHP_EOL . $exception->getMessage();
            }

        }

        return new HtmlResponse($response);
    }
}
