<?php

namespace App\Console\Actions;


use App\Console\Actions\Interfaces\BaseAction;
use App\Models\Model;
use Framework\Services\DBPostgres;
use Framework\Tools\Arr;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Laminas\Diactoros\Response\HtmlResponse;

class Seeder extends BaseAction
{
    protected function getSeeders(): array
    {
        return [
            'lib_contacts' => require '/www/database/seeds/lib_contacts.php',
        ];
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $db = DBPostgres::getInstance();
        $response = '';

        foreach ($this->getSeeders() as $tableName => $seeder) {
            // update or create
            $result = Model::create($seeder['rows'], $seeder['primaryColumns'], $tableName, false);

            $response .= "$tableName updated ".count($result)." rows".PHP_EOL;
//            $db->query('UPDATE '.$tableName.' '.PHP_EOL.
//                'SET ('.implode(', '.PHP_EOL, array_keys($seeder['rows'][0])).') '.PHP_EOL.
//                'VALUES '.implode(', '.PHP_EOL, array_map(function($row) use ($db) {
//                    $columnPairs = [];
//
//                    foreach ($row as $key => $value) {
//                        $columnPairs[] = $db->query($key) .'='. $db->query($value);
//                    }
//
//                    return '('.implode(', '.PHP_EOL, $columnPairs).')';
//                },$seeder['rows'])) .PHP_EOL.
//                'ON DUPLICATE KEY '
//            );

//            echo "\n$tableName\n";
//            print_r($result);
//            print_r($seeder['primaryColumns']);
//            print_r(Arr::only($result, $seeder['primaryColumns']));
        }

        return new HtmlResponse($response);
    }
}
