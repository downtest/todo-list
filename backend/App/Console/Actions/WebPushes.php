<?php

namespace App\Console\Actions;


use App\Console\Actions\Interfaces\BaseAction;
use App\Models\Collection;
use App\Models\User;
use App\Services\FireBase;
use Framework\Services\Config;
use Framework\Services\DBMongo;
use Framework\Services\DBPostgres;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class WebPushes extends BaseAction
{
    protected array $params = [
        'delay' => 3,
        'byStep' => 50, // limit, по сколько коллекций просматривать за шаг
        'ttl' => 900, // сколько секунд команда будет жить
    ];

    protected DBMongo $dbMongo;

    protected DBPostgres $dbPostgres;

    public function __construct()
    {
        $this->dbPostgres = DBPostgres::getInstance();
        $this->dbMongo = DBMongo::getInstance();
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $start = time();
        echo (new \DateTime('now', new \DateTimeZone('Europe/Moscow')))->format('d.m.Y H:i:s')."\n";

        while (true) {
            $totalCollections = $this->dbPostgres->get("SELECT COUNT(*)
                FROM ".Collection::$table." 
                WHERE is_own = true")[0]['count'];
            $stepsAmount = ceil($totalCollections / $this->params['byStep']);
            $step = 0;

            while ($step < $stepsAmount) {
                $this->loadCollections($step++);
            }

            if (time() - $start >= $this->params['ttl']) {
                exit();
            }

            sleep($this->params['delay']);
        }
    }

    protected function loadCollections(?int $step = 0)
    {
        $limit = $this->params['byStep'];
        $offset = $limit * $step;
        $date = date('Y-m-d');

        $maxTime = (new \DateTime('+10min'))->format('H:i');
        $minTime = (new \DateTime('now'))->format('H:i');

//        echo "step $step\n";

        try {
            $collections = $this->getCollections($limit, $offset);

            foreach ($collections as $collectionName => $tokens) {
                $tasksToInform = $this->dbMongo->find($collectionName, [
                    'date_utc' => $date,
                    'time_utc' => ['$gte' => $minTime, '$lte' => $maxTime],
                    'informed' => ['$type' => 'null'],
                ]);

                if (!count($tasksToInform)) {
                    continue;
                }

//                print_r("collection {$collectionName}\n");

                foreach ($tasksToInform as $task) {
                    $result = FireBase::getInstance()->sendMulticast(
                        'LisToDo.ru',
                        $task['time'].' '.explode(PHP_EOL, $task['message'])[0],
                        $tokens,
                        Config::get('app.host').'/item/'.$task['id']
                    );

                    if ($result) {
                        $this->dbMongo->updateOne($collectionName, [
                            'id' => $task['id'],
                            'informed' => date('Y-m-d H:i:s'),
                        ]);

                        echo date('Y-m-d H:i:s')." $collectionName, отправлено: $result\n";
                    }
                }
            }
        } catch (\Throwable $exception) {
            print_r("Error! " . $exception->getMessage() . "\n");
        }
    }

    protected function getCollections(int $limit, int $offset): array
    {
        $result = [];
        $tokens = $this->dbPostgres->get("SELECT col.id as collection_id, col.owner_id, tokens.token
            FROM ".User\UserFirebaseToken::$table." tokens
            LEFT JOIN ".Collection::$table." col ON tokens.user_id = col.owner_id 
            WHERE col.is_own = true 
            LIMIT {$limit} OFFSET {$offset}");

        foreach ($tokens as $row) {
            $collectionName = Collection::computeCollectionName($row['owner_id'], $row['collection_id']);

            $result[$collectionName][] = $row['token'];
        }

        return $result;
    }
}
