<?php


namespace App\Http\BusinessServices;

use App\Models\Collection;
use App\Models\User;
use App\Models\UserToken;
use Framework\Services\DBPostgres;
use Framework\Services\Headers;
use Framework\Services\Interfaces\Service;
use Framework\Services\Session;

/**
 * @package App\Http\BusinessServices
 * @method static Login getInstance
 */
class Login extends Service
{
    protected static $instance;

    protected DBPostgres $db;

    protected ?array $user = null;

    public function __construct()
    {
        $this->db = DBPostgres::getInstance();
    }

    public function setUser(?array $user)
    {
        $this->user = $user;

        return $this;
    }

    public function byEmail(string $email, string $password): array
    {
        if (!$this->user) {
            // Запрашиваем юзера в БД
            $this->user = User::first('SELECT users.* 
                FROM users 
                LEFT JOIN user_contacts ON users.id = user_contacts.user_id
                LEFT JOIN lib_contacts ON lib_contacts.id = user_contacts.contact_id
                WHERE lib_contacts.name = ? 
                  AND user_contacts.value = ? 
                  AND users.password = ?
                ', [
                'email',
                $email,
                Registration::hash($password)
            ]);
        }

        if (!$this->user) {
            // Юзер не найден
            return [
                'status' => false,
                'errors' => ['email' => ["Не найден пользователь по email`у {$email}, либо пароль не совпадает"]],
            ];
        }

        return $this->getLoginData();
    }

    public function getLoginData(): array
    {
        if (!$this->user) {
            throw new \Exception("Не задан пользователь");
        }

        // Проверяем токен
        if ($tokens = $this->db->get('SELECT * 
            FROM user_tokens 
            WHERE (expire_at < CURRENT_TIMESTAMP OR expire_at IS NULL)
              AND user_id = ?',
            [$this->user['id']]
        )) {
            // Токены есть
            $token = $tokens[0];
        } else {
            // Создаём токен
            $token = UserToken::create([[
                'token' => uniqid(),
                'user_id' => $this->user['id'],
                'device_header' => Headers::getInstance()->get('User-Agent'),
                'expire_at' => null,
            ]]);
        }

        // Получаем коллекции
        $userCollections = Collection::query("SELECT * FROM ".Collection::$table." WHERE owner_id = {$this->user['id']} ORDER BY created_at");

        return [
            'status' => true,
            'user' => $this->user,
            'token' => $token['token'],
            'collections' => $userCollections ?? [],
            'currentCollection' => array_filter($userCollections ?? [], fn ($collection) => $collection['is_own'])[0] ?? null,
        ];
    }

}
