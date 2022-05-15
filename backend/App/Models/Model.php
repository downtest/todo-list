<?php


namespace App\Models;


use Framework\Services\DBPostgres;

abstract class Model
{
    /**
     * @var string
     */
    public static string $table;

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
     * @param array $upsertConflictTarget Если передан этот массив(например, [id, name]), то запрос будет проверять в БД колонки из массива и делать update, если значения из колонок уже есть в БД. Передача параметра сделает из запроса UPSERT
     * @return array
     * @throws \Exception
     */
    public static function create(array $array, array $upsertConflictTarget = []): array
    {
        if (!static::$db) {
            static::$db = DBPostgres::getInstance();
        }

        if (empty($array[0])) {
            throw new \Exception("При создании записей передано пустое значение в качестве записи");
        }

        $sql = "INSERT INTO ".static::$table.' ';
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
            $sql .= ' ON CONFLICT ('.implode(',', $upsertConflictTarget).') DO UPDATE SET ';

            $columnsToUpdate = [];

            foreach (array_keys($array[0]) as $column) {
                $columnsToUpdate[] = "$column = excluded.$column";
            }

            $sql .= implode(',', $columnsToUpdate);
        }

        $sql .= ' RETURNING *';

        return static::$db->query($sql);
    }
}
