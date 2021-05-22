<?php


namespace Framework\Services\DBState\Drivers\Sql;


use Exception;

class SqlIndex
{
    /**
     * @var string
     */
    protected string $tableName;

    /**
     * Текущее состояние
     * @var array
     */
    protected array $currentState = [];

    /**
     * Состояние, которое должно быть
     * @var array
     */
    protected array $state = [];

    /**
     * @var mixed
     */
    protected $indexes = [];

    /**
     * @var mixed
     */
    protected $currentIndexes = [];

    /**
     * SqlColumn constructor.
     * @param string $tableName
     * @param array $state
     * @param array $currentState
     */
    public function __construct(string $tableName, array $state, array $currentState = [])
    {
        $this->tableName = $tableName;
        $this->state = $state;
        $this->indexes = $state['indexes'] ?? [];
        $this->currentState = $currentState;
        $this->currentIndexes = $currentState['indexes'] ?? [];
    }

    public function getModifyQueries(): array
    {
        $result = ['createSubQueries' => [], 'delete' => []];

        if ($dropQuery = $this->getDeleteIndexesQuery()) {
            $result['delete'] = $dropQuery;
        }

        foreach ($this->indexes as $indexName => $params) {
            if (count($params['columns']) === 1 && isset($this->state['primary']) && $this->state['primary']) {
                // Primary индексы нужно пропустить, потому что они не попадают в currentState. Возможно, они хранятся у постгреса в других таблицах
                continue;
            }

            $uniqueStr = !empty($params['unique']) ? 'UNIQUE' : '';

            if ($this->indexIsNew($indexName)) {
                $result['create'][] = "CREATE {$uniqueStr} INDEX {$indexName} ON {$this->tableName} (".implode(',', $params['columns']).")";
            } elseif ($this->indexIsChanged($indexName)) {
                $result['delete'][] = "DROP INDEX {$indexName}";
                $result['create'][] = "CREATE {$uniqueStr} INDEX {$indexName} ON {$this->tableName} (".implode(',', $params['columns']).")";
            }
        }

//        print_r($result['createSubQueries']);

        return $result;
    }

    /**
     * @return ?array
     */
    public function getDeleteIndexesQuery(): ?array
    {
        $unnecessaryTables = array_diff(array_keys($this->currentIndexes), array_keys($this->indexes));

        if (!$unnecessaryTables) {
            return null;
        }

        return [
            'ALTER TABLE '.$this->tableName.' DROP CONSTRAINT IF EXISTS ' . implode(',', $unnecessaryTables),
            'DROP INDEX '.implode(',', $unnecessaryTables),
        ];
    }

    protected function indexIsNew(string $indexName): bool
    {
        return empty($this->currentIndexes[$indexName]) && isset($this->indexes[$indexName]);
    }

    protected function indexIsChanged(string $indexName): bool
    {
        if (!isset($this->currentIndexes[$indexName]) || !isset($this->indexes[$indexName])) {
            throw new Exception("Индекс $indexName не существует в текущем состоянии, либо в нужном и поэтому нельзя сказать был ли он изменён");
        }

        foreach ($this->currentIndexes[$indexName] as $prop => $value) {
            if (!isset($this->indexes[$indexName][$prop])) {
                // Не указано свойство в схеме
                continue;
            }

            if ($this->currentIndexes[$indexName][$prop] !== $this->indexes[$indexName][$prop]) {
                return true;
            }
        }

        return false;
    }
}
