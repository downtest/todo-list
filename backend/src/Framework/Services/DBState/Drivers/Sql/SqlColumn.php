<?php


namespace Framework\Services\DBState\Drivers\Sql;


use Exception;

class SqlColumn
{
    protected string $tableName;

    protected string $columnName;

    protected array $aliases = [
        ['varchar', 'string', 'str', 'character varying'],
        ['int', 'integer'],
        ['timestamp', 'timestamp without time zone'],
        ['boolean', 'bool'],
    ];

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
     * SqlColumn constructor.
     * @param string $tableName
     * @param string $columnName
     * @param array $state
     * @param array $currentState
     */
    public function __construct(string $tableName, string $columnName, array $state, array $currentState = [])
    {
        $this->tableName = $tableName;
        $this->columnName = $columnName;
        $this->currentState = $currentState;
        $this->state = $state;
    }

    public function getCreateQuery(array $props = null): string
    {
        if (!$props) {
            $props = $this->state;
        }

        $sql = '';
        $columnType = $this->standardType($props['type']);

        if (isset($props['max_length']) && !in_array($columnType, ['int', 'int8', 'smallint', 'bigint', 'serial', 'bigserial'])) {
            $columnType .= "({$props['max_length']})";
        }

        $columnProps = [
            $this->columnName,
            $columnType,
            isset($props['is_nullable']) ? 'NULL' : 'NOT NULL',
        ];

        if (isset($props['column_default'])) {
            $columnProps[] = 'DEFAULT ' . $props['column_default'];
        }

        if (!empty($props['primary'])) {
            $columnProps[] = 'PRIMARY KEY';
        }

        if (!empty($props['foreign'])) {
            $columnProps[] = $this->getForeignCreateQuery($props['foreign'], false);
        }

        $sql .= implode(' ', $columnProps);

        return $sql;
    }

    public function getModifyQuery(array $props = null): array
    {
        // Дока: https://postgrespro.ru/docs/postgrespro/9.5/sql-altertable

        $modifiedColumnsArr = [];
        $result = ['create' => [],'delete' => [], 'modify' => []];

        foreach ($props as $prop => $value) {
            switch ($prop) {
                case 'autoincrement':
                    if ($value) {
                        // Устанавливаем autoincrement в true
                        $modifiedColumnsArr[] = "ALTER COLUMN \"{$this->columnName}\" SET DEFAULT {$props['column_default']}\n";
                        $result['create'][] = "CREATE SEQUENCE IF NOT EXISTS {$this->tableName}_{$this->columnName}_seq OWNED BY {$this->tableName}.{$this->columnName}";
                    } else {
                        $modifiedColumnsArr[] = "ALTER COLUMN \"{$this->columnName}\" SET DEFAULT null\n";
                        $result['delete'][] = "DROP SEQUENCE IF EXISTS {$this->tableName}_{$this->columnName}_seq CASCADE";
                    }

                    break;
                case 'ordinal_position':
                    break;
                case 'column_default':
                    if (isset($value)) {
                        $modifiedColumnsArr[] = "ALTER COLUMN \"{$this->columnName}\" SET DEFAULT {$value}\n";
                    }
                    break;
                case 'is_nullable':
                    if ($value) {
                        $modifiedColumnsArr[] = "ALTER COLUMN \"{$this->columnName}\" DROP NOT NULL\n";
                    } else {
                        $modifiedColumnsArr[] = "ALTER COLUMN \"{$this->columnName}\" SET NOT NULL\n";
                    }
                    break;
                case 'type':
                    $value = $this->standardType($value);

                    // Приведение текущих значений колонок в новый тип
                    $using = "USING $this->columnName::$value";

                    $modifiedColumnsArr[] = "ALTER COLUMN \"{$this->columnName}\" SET DATA TYPE {$value} {$using};\n";
                    break;
                case 'max_length':
                    if (!empty($this->state['type']) && in_array($this->state['type'], ['varchar', 'character varying'])) {
                        $length = $value ? "({$value})" : '';

                        $modifiedColumnsArr[] = "ALTER COLUMN \"{$this->columnName}\" SET DATA TYPE character varying {$length};\n";
                    }
                    break;
                case 'indexes':
                    $indexChanges = (new SqlIndex($this->tableName, $this->state, $this->currentState))->getModifyQueries();
                    $result['delete'] = array_merge($result['delete'], $indexChanges['delete']);

                    if (!empty($indexChanges['create'])) {
                        $result['create'] = array_merge($result['create'], $indexChanges['create']);
                    }
                    if (!empty($indexChanges['createSubQueries'])) {
                        $modifiedColumnsArr[] = implode(',' . PHP_EOL, $indexChanges['createSubQueries']);
                    }

                    break;
                case 'foreign':
                    if ($foreignSubQuery = $this->getForeignModifyQuery($this->state[$prop] ?? [], $this->currentState[$prop])) {
                        $modifiedColumnsArr[] = $foreignSubQuery;
                    }
                    break;
            }
        }

        if ($modifiedColumnsArr) {
            $result['modify'][] = 'ALTER TABLE ' . $this->tableName . PHP_EOL . implode(PHP_EOL . ',', $modifiedColumnsArr);
        }

        return $result;
    }

    public function getModifyQueries(): array
    {
        $result = ['create' => [],'delete' => [], 'modify' => []];

        if (!$this->currentState) {
            // Совершенно новая колонка
            if ($createColumnsStr = $this->getCreateQuery()) {
                $result['create'][] = 'ALTER TABLE ' . $this->tableName . PHP_EOL . ' ADD COLUMN ' . $createColumnsStr;
            }
        } elseif ($this->isChanged()) {
            // Колонку надо изменить
            $modifyQueries = $this->getModifyQuery($this->getChangedProperties());

            $result['create'] = array_merge($result['create'], $modifyQueries['create']);
            $result['delete'] = array_merge($result['delete'], $modifyQueries['delete']);
            $result['modify'] = array_merge($result['modify'], $modifyQueries['modify']);
        }

        return $result;
    }

    protected function standardType(string $type): string
    {
        if (in_array($type, ['int2'])) {
            return 'smallint';
        } elseif (in_array($type, ['int', 'int4', 'integer'])) {
            return 'int';
        } elseif (in_array($type, ['big_int', 'bigInt', 'int8'])) {
            return 'bigint';
        } elseif (in_array($type, ['string'])) {
            return 'varchar';
        } else {
            return $type;
        }
    }

    /**
     * Возвращает все свойства, которые нужно поменять
     *
     * @return array
     */
    protected function getChangedProperties(): array
    {
        $changedProps = [];
        // Свойства, которые могут иметь значение null (В поле max_length может быть null как значение)
        $nullableProperties = ['max_length'];

        foreach ($this->currentState as $prop => $value) {
            if (!in_array($prop, $nullableProperties) && !$this->currentState[$prop] && empty($this->state[$prop])) {
                continue; // Изменений нет
            }

            switch ($prop) {
                case 'autoincrement':
                    if (($this->state['autoincrement'] ?? false) !== $this->currentState['autoincrement']) {
                        $changedProps['autoincrement'] = $this->state['autoincrement'];
                        $changedProps['column_default'] = $this->state['autoincrement'] ? "nextval('{$this->tableName}_{$this->columnName}_seq'::regclass)" : '';
                    }

                    break;
                case 'column_default':
                    if (isset($this->state['autoincrement']) && $this->state['autoincrement']) {
                        // Когда поле autoincrement true, то тут будет nextval(seq_name::regclass) и менять его не нужно
                        break;
                    } elseif ($this->state[$prop] !== $value) {
                        $changedProps[$prop] = $this->state[$prop];
                        break;
                    }
                case 'ordinal_position':
                    // TODO: сделать сортировку надо бы, но Postgres, по-моему, не поддерживает в запросе alter table очерёдность колонок(но это не точно)
                    break;
                case 'type':
                    if ($this->state[$prop] === $value) {
                        // Изменений нет
                        break;
                    }

                    foreach ($this->aliases as $similars) {
                        if (in_array($this->state[$prop], $similars) && in_array($value, $similars)) {
                            // Значения разнятся, но они синонимы(из одного массива в $this->aliases)
                            break 2; // Выходим из цикла foreach, и из конструкции switch
                        }
                    }

                    $changedProps[$prop] = $this->state[$prop];
                    break;
                case 'max_length':
                    if (!empty($this->state[$prop]) && (int)$this->state[$prop] !== (int)$value) {
                        $changedProps[$prop] = $this->state[$prop];// Изменений нет
                    }
                    break;
                default:
                    if (empty($this->state[$prop])) {
                        $changedProps[$prop] = null;
                    } elseif ($this->state[$prop] !== $value) {
                        $changedProps[$prop] = $this->state[$prop];
                    }
            }
        }

        return $changedProps;
    }

    protected function isChanged(): bool
    {
        return count($this->getChangedProperties()) > 0;
    }

    protected function getChangedForeignProps(array $state, array $currentState = []): array
    {
        return array_keys(array_diff_assoc($state, $currentState));
    }

    /**
     * @param array $foreignProps
     * @return string
     * @throws Exception
     */
    protected function getForeignCreateQuery(array $foreignProps, bool $add = false): string
    {
        if (empty($foreignProps['name'])
            || empty($foreignProps['foreign_table'])
            || empty($foreignProps['foreign_column'])
        ) {
            throw new Exception('При описании внешнего ключа должны быть указаны свойства: name, foreign_table, foreign_column');
        }

        $foreignSql = ($add ? 'ADD ' : '') . "CONSTRAINT {$foreignProps['name']} " . ($add ? "FOREIGN KEY ({$this->columnName})" : '')
                ."REFERENCES {$foreignProps['foreign_table']} ({$foreignProps['foreign_column']})";

        if (isset($foreignProps['on_update'])) {
            $foreignSql .= ' ON UPDATE ' . $foreignProps['on_update'];
        }

        if (isset($foreignProps['on_delete'])) {
            $foreignSql .= ' ON DELETE ' . $foreignProps['on_delete'];
        }

        return $foreignSql;
    }

    /**
     * Возвращает инструкции SQL для изменения внешнего(foreign) ключа
     *
     * @param array $state
     * @param array $currentState
     * @return string
     * @throws Exception
     */
    protected function getForeignModifyQuery(array $state, array $currentState = []): ?string
    {
        $result = null;

        if (!$state) {
            return 'DROP CONSTRAINT ' . $currentState['name'];
        }

        if (!$changes = $this->getChangedForeignProps($state, $currentState)) {
            return null;
        }

        if (count($changes) === 1 && in_array('name', $changes)) {
            // Нужно только переименовать foreign
            return "RENAME CONSTRAINT {$currentState['name']} to {$state['name']}";
        }

        // Поменялось не только имя и т.к. у постгреса менять ограничения нельзя(constraint), то надо пересоздать его
        return 'DROP CONSTRAINT ' . $currentState['name'] . ',' . PHP_EOL . $this->getForeignCreateQuery($state);
    }
}
