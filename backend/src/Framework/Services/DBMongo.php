<?php

namespace Framework\Services;


use Exception;
use Framework\Services\Interfaces\Service;
use Framework\Tools\Arr;
use MongoDB\BSON\ObjectId;

/**
 * Class DBPostgres
 * @package Framework\Services
 * @method static DBMongo getInstance
 */
class DBMongo extends Service
{
    /**
     * @var self
     */
    protected static $instance;

    /**
     * @var DBMongo
     */
    protected $client;

    /**
     * @param string $host
     * @param int $port
     * @param string $database
     * @param string $username
     * @param string $password
     * @param ?array $options
     */
    public function __construct(string $host, int $port, string $database, string $username, string $password, ?array $options = [])
    {
        $this->client = new \MongoDB\Client("mongodb://{$username}:{$password}@{$host}:{$port}");
        $this->client = $this->client->$database;

        return $this;
    }

    /**
     * @param string $connection
     * @return self
     * @throws Exception
     */
    protected static function createInstance(string $connection = 'mongo'): self
    {
        $connectionData = Config::get("database.$connection");

        if (!$connectionData) {
            throw new Exception("Не найдено соединение $connection");
        }

        return new self(
            $connectionData['host'],
            $connectionData['port'],
            $connectionData['database'],
            $connectionData['username'],
            $connectionData['password'],
            $connectionData['options'] ?? []
        );
    }

    /**
     * Возвращаем выборку из БД
     *
     * @param string|null $collectionName
     * @param array $conditions
     * @param array $options
     * @return array
     */
    public function find(?string $collectionName, array $conditions = [], array $options = []): array
    {
        $result = [];

        if ($collectionName) {
            $cursor = $this->client->$collectionName->find($conditions, $options);

            foreach ($cursor as $value) {
                $result[] = ['id' => (string)$value['_id']] + $value->getArrayCopy();
            }
        }

        return $result;
    }

    public function findById(string $collectionName, string $id): ?array
    {
        try {
            $id = new ObjectId($id);
        } catch (\Throwable $exception) {
            throw new Exception("ID $id не является корректным");
        }

        $cursor = $this->client->$collectionName->find(['_id' => $id])->toArray()[0];

        if (!$cursor) {
            return null;
        }

        $resultArray = $cursor->getArrayCopy();

        return ['id' => (string)$resultArray['_id']] + $resultArray;
    }

    public function insertOne(string $collectionName, array $payload = []): string
    {
        return (string)$this->client->$collectionName->insertOne($payload)->getInsertedId();
    }

    public function updateOne(string $collectionName, array $payload): bool
    {
        $taskId = $payload['taskId'] ?? $payload['id'] ?? null;

        if (!$taskId) {
            throw new Exception('Не передан ID таски');
        }

        return $this->client->$collectionName->updateOne(
            ['_id' => new ObjectId($taskId)],
            ['$set' => $payload]
        )->getModifiedCount() > 0;
    }

    public function updateMany(string $collectionName, array $filter, array $payload): int
    {
        return $this->client->$collectionName->updateMany(
            $filter,
            $payload
        )->getModifiedCount();
    }

    public function deleteMany(string $collectionName, array $filter, array $options = []): int
    {
        return $this->client->$collectionName->deleteMany($filter, $options)->getDeletedCount();
    }
}
