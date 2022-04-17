<?php


namespace Framework\Services\DBState\Drivers;


use Exception;
use Framework\Services\DBPostgres;
use Framework\Services\DBState\Drivers\Interfaces\DriverInterface;
use Framework\Services\DBState\Drivers\Sql\SqlTable;

class PostgresDriver extends DriverInterface
{
    /**
     * @var DBPostgres
     */
    protected DBPostgres $db;
    protected array $currentState;

    public function __construct()
    {
        $this->db = DBPostgres::getInstance();

        return $this;
    }

    /**
     * Загружаем из БД текущее состояние базы
     *
     * @return array
     * @throws Exception
     */
    public function loadCurrentState(): array
    {
        $columns = $this->db->query("select * 
            from information_schema.columns 
            WHERE table_schema = 'public';");

        $this->currentState = [];

        foreach ($columns as $column) {
            $this->currentState[$column['table_name']][$column['column_name']] = [
                'autoincrement' => (strpos($column['column_default'], 'nextval') !== false),
                'ordinal_position' => $column['ordinal_position'],
                'column_default' => $column['column_default'],
                'is_nullable' => ($column['is_nullable'] === 'YES'),
                'type' => $column['data_type'],
                'max_length' => $column['character_maximum_length'],
                'primary' => false,
                'indexes' => [],
                'foreign' => [],
//                'others' => $column,
            ];
        }

        // Сортировка колонок в таблице
        foreach ($this->currentState as &$table) {
            uasort ($table, function($a, $b) {
                return ($a['ordinal_position'] > $b['ordinal_position']) ? 1 : -1;
            });
        }

        $this->loadPrimary();
        $this->loadIndexes();
        $this->loadForeigns();

        return $this->currentState;
    }

    /**
     * Устанавливает первичные ключи для текущего состояния БД
     *
     * @throws Exception
     */
    protected function loadPrimary(): void
    {
        if (!$tables = array_keys($this->currentState)) {
            return;
        }

        $primaryKeys = $this->db->query("SELECT a.attname, format_type(a.atttypid, a.atttypmod) AS data_type, pg_class.relname AS table
            FROM   pg_index i
            JOIN   pg_attribute a ON a.attrelid = i.indrelid
                                 AND a.attnum = ANY(i.indkey)
            LEFT JOIN pg_class ON i.indrelid = pg_class.oid
            WHERE  i.indrelid IN (".
                implode(',', array_map(function($table) {
                    return "'{$table}'::regclass";
                },$tables))
            .")
            AND    i.indisprimary;");

        foreach ($primaryKeys as $primaryKey) {
            $this->currentState[$primaryKey['table']][$primaryKey['attname']]['primary'] = true;
        }
    }

    protected function loadIndexes(): void
    {
        $indexes = $this->db->query("select * 
            from pg_indexes
            where schemaname = 'public'");

        foreach ($indexes as $index) {
            $data = [
                'unique' => strpos($index['indexdef'], 'UNIQUE') !== false,
                'columns' => explode(',', str_replace(' ', '', substr(
                    $index['indexdef'],
                    strpos($index['indexdef'], '(') + 1,
                    strpos($index['indexdef'], ')') - strpos($index['indexdef'], '(') - 1
                ))),
            ];

            // Ставим индекс только для тех, которые не являются primary. Для Primary индекс делается автоматически
            if (empty($this->currentState[$index['tablename']][$data['columns'][0]]['primary'])) {
                $this->currentState[$index['tablename']][$data['columns'][0]]['indexes'][$index['indexname']] = $data;
            }
        }
    }

    protected function loadForeigns(): void
    {
        $foreignKeys = $this->db->query("SELECT
                tc.table_schema, 
                tc.constraint_name, 
                tc.table_name, 
                kcu.column_name, 
                ccu.table_schema AS foreign_table_schema,
                ccu.table_name AS foreign_table_name,
                ccu.column_name AS foreign_column_name,
                rc.update_rule AS on_update,
                rc.delete_rule AS on_delete
            FROM 
                information_schema.table_constraints AS tc 
                JOIN information_schema.key_column_usage AS kcu
                  ON tc.constraint_name = kcu.constraint_name
                  AND tc.table_schema = kcu.table_schema
                JOIN information_schema.constraint_column_usage AS ccu
                  ON ccu.constraint_name = tc.constraint_name
                  AND ccu.table_schema = tc.table_schema
                JOIN information_schema.referential_constraints AS rc 
                  ON ccu.constraint_name = rc.constraint_name
                  AND ccu.table_schema = rc.constraint_schema
            WHERE tc.constraint_type = 'FOREIGN KEY';");

        foreach ($foreignKeys as $foreignKey) {
            $this->currentState[$foreignKey['table_name']][$foreignKey['column_name']]['foreign'] = [
                'name' => $foreignKey['constraint_name'],
                'foreign_table' => $foreignKey['foreign_table_name'],
                'foreign_column' => $foreignKey['foreign_column_name'],
                'on_update' => $foreignKey['on_update'],
                'on_delete' => $foreignKey['on_update'],
            ];
        }
    }

    public function deleteTables(array $state): ?string
    {
        $unnecessaryTables = array_diff(array_keys($this->currentState), array_keys($state));

        if (!$unnecessaryTables) {
            return null;
        }

        $sql = 'DROP TABLE ' . implode(',', $unnecessaryTables);

        return $sql;
    }

    public function createTables(array $state): ?array
    {
        $newTables = array_diff(array_keys($state), array_keys($this->currentState));
        $result = [];

        if (!$newTables) {
            return null;
        }

        foreach ($newTables as $table) {
            $sql = (new SqlTable($table, $state[$table]))->create();

            $result[] = $sql;
        }

        return $result;
    }

    public function modifyTables(array $state): array
    {
        $existedTables = array_keys(array_intersect_key($state, $this->currentState));
        $result = ['delete' => [], 'create' => [], 'modify' => []];

        foreach ($existedTables as $table) {
            // Удаляем ненужные колонки
            $dropColumnsSql = (new SqlTable(
                $table,
                $state[$table],
                $this->currentState[$table]
            ))->deleteUnnecessaryColumns();

            if ($dropColumnsSql) {
                $result['delete'][] = $dropColumnsSql;
            }

            // Изменяем колонки
            $modifySql = (new SqlTable(
                $table,
                $state[$table],
                $this->currentState[$table]
            ))->getModifyQueries();

            if ($modifySql) {
                $result['create'] = array_merge($result['create'], $modifySql['create']);
                $result['delete'] = array_merge($result['delete'], $modifySql['delete']);
                $result['modify'] = array_merge($result['modify'], $modifySql['modify']);
            }
        }

        return $result;
    }

}
