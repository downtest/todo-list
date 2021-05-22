<?php

namespace Framework\Services;


use Exception;
use Framework\Services\Interfaces\Service;
use PDO;

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
     * @param string|null $connection
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
            $connectionData['options']
        );
    }

    public function find(string $collectionName, array $conditions = []): array
    {
        $result = [];
        $cursor = $this->client->$collectionName->find($conditions);

        foreach ($cursor as $value) {
            $result[] = $value;
        }

        return $result;
    }
}
