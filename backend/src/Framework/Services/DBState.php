<?php

namespace Framework\Services;


use Exception;
use Framework\Services\DBState\Drivers\Interfaces\DriverInterface;
use Framework\Services\DBState\Drivers\PostgresDriver;
use Framework\Services\Interfaces\Service;

class DBState extends Service
{
    protected array $state = [];

    protected array $currentState = [];

    /**
     * @var DriverInterface
     */
    protected DriverInterface $driver;

    /**
     * DBState constructor.
     * @param string $driverName
     * @param string $stateName
     * @throws Exception
     */
    public function __construct(string $driverName, string $stateName = 'default')
    {
        $this->driver = $this->getDriver($driverName);
        $this->currentState = $this->driver->loadCurrentState();

        $this->loadState($stateName);

        return $this;
    }

    public function getQueries(): array
    {
        $queries = [];

        if ($deleteQuery = $this->driver->deleteTables($this->state)) {
            $queries[] = $deleteQuery;
        }

        if ($createTableQueries = $this->driver->createTables($this->state)) {
            $queries = array_merge($queries, $createTableQueries);
        }

        if ($modifyTableQueries = $this->driver->modifyTables($this->state)) {
            $queries = array_merge($queries, $modifyTableQueries['delete'], $modifyTableQueries['create'], $modifyTableQueries['modify']);
        }

        return $queries;
    }

    /**
     * Загрузка схемы (какой база должна быть)
     *
     * @param string $name
     * @throws Exception
     */
    public function loadState(string $name = 'default'): void
    {
        $configPath = realpath(__DIR__ . '/../../../database/states');
        $fullPath = "{$configPath}/{$name}.php";

        if (!file_exists($fullPath)) {
            throw new Exception("No state {$fullPath} found");
        }

        $this->state = require $fullPath;
    }

    /**
     * @param string $driverName
     * @return DriverInterface
     * @throws Exception
     */
    protected function getDriver(string $driverName): DriverInterface
    {
        switch ($driverName) {
            case 'postgres':
                return new PostgresDriver();
        }

        throw new \Exception("DBState не нашёл драйвера под именем {$driverName}");
    }
}
