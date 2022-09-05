<?php


namespace App\Services;

use App\Models\Collection;
use App\Models\User;
use App\Models\UserToken;
use Framework\Services\DBPostgres;
use Framework\Services\Interfaces\Service;

/**
 * Класс, хранящий бизнес-логику тасок
 * @package App\Http\BusinessServices
 */
class Registration extends Service
{
    protected static $instance;

    protected DBPostgres $db;

    protected string $email;

    /**
     * @var array|null
     */
    protected ?array $existedUser = null;

    public function __construct()
    {
        $this->db = DBPostgres::getInstance();
    }

    public function setEmail(string $email)
    {
        $this->email = $email;

        return $this;
    }

    public function getExistedUser()
    {
        if ($this->email || ($this->existedUser && $this->existedUser['email'] !== $this->email)) {
            $this->existedUser = User::findByEmail($this->email);
        }

        return $this->existedUser;
    }

    public function byEmail(string $email, string $password): ?array
    {
        $this->setEmail($email);

        $existedUser = $this->getExistedUser();

        if ($existedUser && !empty($existedUser['password'])) {
            $existedHashedPassword = self::hash($existedUser['password']);
        }

        $userHashedPassword = self::hash($password);

        if ($existedUser && isset($existedHashedPassword) && $existedHashedPassword === $userHashedPassword) {
            // Пользователь есть, пароли совпадают
            return $existedUser;
        } elseif ($existedUser && isset($existedHashedPassword) && $existedHashedPassword !== $userHashedPassword) {
            // Пользователь есть, но пароль другой
            throw new \Exception("Пользователь {$email} есть, но пароли не совпадают");
        } else {
            // Выходит, что пользователя нет, либо нет пароля
            $user = $this->db->get('INSERT INTO '.User::$table.' (password) VALUES (?) RETURNING *', [$userHashedPassword])[0] ?? null;

            if ($contact = User\UserContact::first('SELECT * FROM lib_contacts WHERE name = ?', ['email'])) {
                $emailContact = $this->db->get('INSERT INTO '.User\UserContact::$table.' (contact_id, user_id, value) VALUES (?, ?, ?) RETURNING *', [$contact['id'], $user['id'], $email])[0] ?? null;
            }

            if (!$user) {
                throw new \Exception("Не удалось зарегистрировать пользователя");
            }

            // Создание коллекции
            $this->db->get('INSERT INTO '.Collection::$table.' (id, name, owner_id, created_at, is_own) VALUES (?, ?, ?, ?, ?)', [
                uniqid(),
                'Мои записи',
                $user['id'],
                'NOW()',
                'true',
            ]);

            // Создание токена
            $this->db->get('INSERT INTO '.UserToken::$table.' (token, user_id, device_header, created_at) VALUES (?, ?, ?, ?)', [
                uniqid(),
                $user['id'],
                $_SERVER['HTTP_USER_AGENT'],
                'NOW()',
            ]);

            return $user;
        }
    }

    public static function hash(string $password)
    {
        return hash('sha256', $password);
    }

}
