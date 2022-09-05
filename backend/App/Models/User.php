<?php


namespace App\Models;


use Framework\Services\DBPostgres;
use Framework\Services\Headers;

class User extends Model
{
    /**
     * @var string|null
     */
    public static ?string $table = 'users';

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

            static::$current = User::first("SELECT ".static::$table.".* 
                FROM ".static::$table."
                LEFT JOIN user_tokens ON ".static::$table.".id = user_tokens.user_id 
                WHERE user_tokens.token = ?", [$token]);
        }

        return static::$current;
    }

    public static function findByEmail(string $email): ?array
    {
        return static::first('SELECT users.* 
                FROM '.User::$table.' 
                LEFT JOIN '.User\UserContact::$table.' ON users.id = user_contacts.user_id
                LEFT JOIN lib_contacts ON lib_contacts.id = user_contacts.contact_id
                WHERE lib_contacts.name = ? 
                    AND user_contacts.value = ?'
        , [
            'email',
            $email,
        ]);
    }

    /**
     * @throws \Exception
     */
    public static function getDefaultCollectionForCurrentUser(): ?string
    {
        if (!static::$current) {
            static::$current = static::current();
        }

        if (!static::$current['id']) {
            return null;
        }

        $collection = DBPostgres::getInstance()->get('SELECT id, owner_id FROM '.Collection::$table.' WHERE owner_id = ? AND is_own = true', [static::$current['id']]);

        if (empty($collection[0])) {
            return null;
        }

        return Collection::computeCollectionName($collection[0]['owner_id'], $collection[0]['id']);
    }

}
