<?php

namespace Framework\Services;


use App\Http\Exceptions\AppException;
use App\Http\Exceptions\SqlException;
use Exception;
use Framework\Services\Interfaces\Service;
use PDO;

/**
 * Class DBPostgres
 * @package Framework\Services
 * @method static DBPostgres getInstance
 */
class DBPostgres extends Service
{
    /**
     * @var self
     */
    protected static $instance;

    /**
     * @var PDO
     */
    protected PDO $pdo;

    /**
     * DBPostgres constructor.
     * @param string $driver
     * @param string $host
     * @param int $port
     * @param string $dbName
     * @param string $username
     * @param string $password
     * @param array $options
     */
    public function __construct(string $driver, string $host, int $port, string $dbName, string $username, string $password, array $options = [])
    {
        $this->pdo = new PDO("{$driver}:host={$host};port={$port};dbname={$dbName};", $username, $password, $options);

        return $this;
    }

    /**
     * @param string|null $connection
     * @return self
     * @throws Exception
     */
    protected static function createInstance(string $connection = null): self
    {
        if (!$connection) {
            $connection = Config::get('database.sql.default');
        }

        $connectionData = Config::get("database.sql.$connection");

        if (!$connectionData) {
            throw new Exception("Не найдено соединение $connection");
        }

        return new self(
            $connectionData['driver'],
            $connectionData['host'],
            $connectionData['port'],
            $connectionData['database'],
            $connectionData['username'],
            $connectionData['password'],
            $connectionData['options']
        );
    }

    public function query(string $sql): array
    {
        try {
            $result = $this->pdo->query($sql, $this->pdo::FETCH_ASSOC);

            if (isset($this->pdo->errorInfo()[2])) {
                throw new Exception($this->pdo->errorInfo()[2]);
            }

            return $result->fetchAll();
        } catch (\Throwable $exception) {
            throw new AppException($exception->getMessage(), 0, null, ['sql' => $sql]);
        }
    }
    public function exec(string $sql): int
    {
        try {
            $result = $this->pdo->exec($sql);

            if (isset($this->pdo->errorInfo()[2])) {
                throw new AppException($this->pdo->errorInfo()[2], 0, null, ['sql' => $sql]);
            }

            return $result;
        } catch (\Throwable $exception) {
            throw new AppException($exception->getMessage(), 0, null, ['sql' => $sql]);
        }
    }

    /**
     * Выполняет подготовленный запрос
     * В подготовленных запросах нужно в качестве параметров передавать ?(если массив параметров обычных) либо :param(если массив  параметров ассоциативный)
     *
     * @param string $sql
     * @param array $params
     * @return bool
     * @throws Exception
     */
    public function prepare(string $sql, array $params = []): bool
    {
        try {
            $query = $this->pdo->prepare($sql);

            if (!$query) {
                throw new Exception("Не удалось подготовить запроc {$sql}");
            }

            return $query->execute($params);
        } catch (\Throwable $exception) {
            throw new AppException($exception->getMessage(), 0, null, ['sql' => $sql, 'params' => $params]);
        }
    }

    /**
     *  Возвращает данные из БД по подготовленному запросу
     *
     * @param string $sql
     * @param array $params
     * @return array
     * @throws Exception
     */
    public function get(string $sql, array $params = []): array
    {
        try {
            $query = $this->pdo->prepare($sql);

            if (!$query) {
                throw new Exception("Не удалось подготовить запроc {$sql}");
            }

            $query->execute($params);

            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Throwable $exception) {
            throw new AppException($exception->getMessage(), 0, null, ['sql' => $sql, 'params' => $params]);
        }
    }

    public function quote(string $arg): string
    {
        return $this->pdo->quote($arg);
    }
}
