<?php


namespace Framework\Services\DBState\Drivers\Sql;


class SqlTable
{
    /**
     * @var string
     */
    protected string $tableName = '';

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
     * SqlTable constructor.
     * @param string $tableName
     * @param array $state
     * @param array $currentState
     */
    public function __construct(string $tableName, array $state, array $currentState = [])
    {
        $this->tableName = $tableName;
        $this->state = $state;
        $this->currentState = $currentState;

        return $this;
    }

    public function create(): string
    {
        $sql = 'CREATE TABLE '.$this->tableName;
        $columnsArr = [];

        foreach ($this->state as $columnName => $column) {
            $columnsArr[] = (new SqlColumn($this->tableName, $columnName, $column))->getCreateQuery();
        }

        return $sql.'('.PHP_EOL.implode(",\n", $columnsArr).PHP_EOL.')';
    }

    public function getModifyQueries(): ?array
    {
        $newColumns = array_keys(array_diff_key($this->state, $this->currentState));
        $existedColumns = array_keys(array_intersect_key($this->state, $this->currentState));
        $result = ['create' => [], 'delete' => [], 'modify' => []];

        foreach (array_merge($newColumns, $existedColumns) as $column) {
            if ($columnQueries = (new SqlColumn(
                    $this->tableName,
                    $column,
                    $this->state[$column],
                    $this->currentState[$column] ?? []
                ))->getModifyQueries()) {
                $result = [
                    'create' => array_merge($result['create'], $columnQueries['create']),
                    'delete' => array_merge($result['delete'], $columnQueries['delete']),
                    'modify' => array_merge($result['modify'], $columnQueries['modify']),
                ];
            }
        }

        if (!$result['create'] && !$result['delete'] && !$result['modify']) {
            return null;
        }

        return $result;
    }

    public function deleteUnnecessaryColumns()
    {
        $unnecessaryColumns = array_diff(array_keys($this->currentState), array_keys($this->state));

        if (!$unnecessaryColumns) {
            return null;
        }

        $unnecessaryColumnsArr = [];

        foreach ($unnecessaryColumns as $column) {
            $unnecessaryColumnsArr[] = "DROP COLUMN {$column}";
        }

        if ($unnecessaryColumnsArr) {
            return "ALTER TABLE {$this->tableName} " . implode(PHP_EOL . ',', $unnecessaryColumnsArr);
        }
    }
}
