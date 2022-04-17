<?php


namespace App\Models;


use Framework\Services\DBPostgres;
use Framework\Services\Headers;
use Framework\Services\Session;

class User extends Model
{
    /**
     * @var string
     */
    public static string $table = 'users';

    const SESSION_KEY = 'user_token';
    const HEADER_KEY = 'X-User-Token';

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
            if (!$token = Headers::getInstance()->get(static::HEADER_KEY)) {
                return null;
            }

            $users = DBPostgres::getInstance()->get("SELECT ".static::$table.".* 
                FROM ".static::$table."
                LEFT JOIN user_tokens ON ".static::$table.".id = user_tokens.user_id 
                WHERE user_tokens.token = ?", [$token]);

            static::$current = $users ? $users[0] : null;
        }

        return static::$current;
    }

}
