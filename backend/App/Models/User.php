<?php


namespace App\Models;


use Framework\Services\Session;

class User extends Model
{
    /**
     * @var string
     */
    public static string $table = 'users';

    const SESSION_KEY = 'user_id';

    /**
     * @var ?array
     */
    protected static ?array $current = null;

    /**
     * @return array|null
     * @throws \Exception
     */
    public static function current(): ?array
    {
        if (!static::$current) {
            if (!$userId = Session::getInstance()->get(static::SESSION_KEY)) {
                return null;
            }

            static::$current = static::find($userId);
        }

        return static::$current;
    }

}
