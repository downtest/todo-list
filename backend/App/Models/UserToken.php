<?php


namespace App\Models;


use Framework\Services\DBPostgres;

class UserToken extends Model
{
    /**
     * @var string
     */
    public static string $table = 'user_tokens';

    /**
     * @throws \Exception
     */
    public static function findByUserId(int $userId): ?array
    {
        return DBPostgres::getInstance()->get('SELECT * 
            FROM '.static::$table.' 
            WHERE user_id = ? 
                AND expire_at IS NULL 
                OR expire_at > CURRENT_TIMESTAMP',
            [$userId]
        )[0] ?? null;
    }
}
