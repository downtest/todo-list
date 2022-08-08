<?php


namespace App\Models;


use App\Http\Exceptions\AppException;
use Framework\Services\DBPostgres;

class Model
{
    /**
     * @var string|null
     */
    public static ?string $table = null;

    /**
     * @var string
     */
    public static string $primaryColumn = 'id';

    /**
     * @var ?DBPostgres
     */
    protected static ?DBPostgres $db = null;

    /**
     * Model constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        if (!static::$db) {
            static::$db = DBPostgres::getInstance();
        }
    }

    /**
     * @param $id
     * @return ?array
     * @throws \Exception
     */
    public static function find($id): ?array
    {
        if (!static::$db) {
            static::$db = DBPostgres::getInstance();
        }

        return static::$db->query("SELECT * FROM ".static::$table." where ".static::$primaryColumn." = ".static::$db->quote($id))[0] ?? null;
    }

    /**
     * @param string $sql
     * @return array
     * @throws \Exception
     */
    public static function query(string $sql): array
    {
        if (!static::$db) {
            static::$db = DBPostgres::getInstance();
        }

        return static::$db->query($sql);
    }

    public static function get(string $sql, array $placeholders = []): array
    {
        if (!static::$db) {
            static::$db = DBPostgres::getInstance();
        }

        return static::$db->get($sql, $placeholders);
    }

    public static function first(string $sql, array $placeholders = []): ?array
    {
        return static::get($sql, $placeholders)[0] ?? null;
    }

    /**
     * @param array $array Массив новых строк (каждая новая строка- ассоциативный массив с названием колонки в ключе)
     * @param array|null $upsertConflictTarget Если передан этот массив(например, [id, name]), то запрос будет проверять в БД колонки из массива и делать update, если значения из колонок уже есть в БД. Передача параметра сделает из запроса UPSERT
     * @param string|null $tableName
     * @param bool $useIndexPredicate
     * @return array
     * @throws \Exception
     */
    public static function create(
        array $array,
        ?array $upsertConflictTarget = [],
        ?string $tableName = null,
        bool $useIndexPredicate = true
    ): array
    {
        if (!static::$db) {
            static::$db = DBPostgres::getInstance();
        }

        if (empty($array[0])) {
            throw new \Exception("При создании записей передано пустое значение в качестве записи");
        }

        if (!$tableName && !static::$table) {
            throw new \Exception("Не задано имя таблицы перед запросом create");
        }

        $sql = "INSERT INTO ".($tableName ?? static::$table).' ';
        $sql .= '('.implode(',', array_keys($array[0])).') VALUES ';
        $columnRows = [];

        foreach ($array as $row) {
            $columnValues = [];

            foreach ($row as $colValue) {
                if (is_null($colValue)) {
                    $columnValues[] = 'NULL';
                } else {
                    $columnValues[] = static::$db->quote($colValue);
                }
            }

            $columnRows[] = '('.implode(',', $columnValues).')';
        }

        $sql .= implode(',', $columnRows);

        // ON CONFLICT (id) DO UPDATE
        if ($upsertConflictTarget) {
            $sql .= ' ON CONFLICT ('.implode(',', $upsertConflictTarget).')'.PHP_EOL;

//            if ($useIndexPredicate) {
//                $indexPredicates = [];
//
//                foreach ($upsertConflictTarget as $column) {
//                    $indexPredicates[] = "(({$column})::text = (excluded.{$column})::text)";
//                }
//
//                $sql .= 'WHERE' .PHP_EOL. implode("\n AND", $indexPredicates);
//            }

            $sql .= ' DO UPDATE SET '.PHP_EOL;

            $columnsToUpdate = [];

            foreach (array_keys($array[0]) as $column) {
                $columnsToUpdate[] = "$column = excluded.$column";
            }

            $sql .= implode(',', $columnsToUpdate);
        }

        $sql .= ' RETURNING *';

        return static::$db->query($sql);
    }

    /**
     * @param array $updateArr Ассоциативный массив новых параметров [name => Peter, age => 23]
     * @param array $conditionArr Массив массивов условий []
     * @return array
     * @throws AppException
     */
    public static function update(array $updateArr, array $conditionArr = []): array
    {
        $sql = 'UPDATE '.static::$table.PHP_EOL;

        if ($updateArr) {
            $sql .= 'SET'.PHP_EOL;

            $setArray = [];

            foreach ($updateArr as $key => $value) {
                $setArray[] = static::$db->quote($key).'=\''.static::$db->quote($value).'\''.PHP_EOL;
            }

            $sql .= implode(','.PHP_EOL, $setArray);
        }

        if ($conditionArr) {
            $sql .= 'WHERE';

            $whereArray = [];

            foreach ($conditionArr as $condition) {
                $whereArray[] = static::$db->quote($key).'=\''.static::$db->quote($value).'\''.PHP_EOL;
            }

            $sql .= implode('AND ', $whereArray);
        }

        return static::$db->query($sql);
    }
}
