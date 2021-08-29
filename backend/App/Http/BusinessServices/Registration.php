<?php


namespace App\Http\BusinessServices;

use App\Models\User;
use Framework\Services\DBPostgres;
use Framework\Services\Interfaces\Service;

/**
 * Класс, хранящий бизнес-логику тасок
 * @package App\Http\BusinessServices
 */
class Registration extends Service
{
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
            $this->existedUser = $this->db->get('SELECT * FROM '.User::$table.' WHERE email = ?', [$this->email])[0] ?? null;
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
            return $this->db->get('INSERT INTO '.User::$table.' (email, password) VALUES (?, ?) RETURNING *', [$email, $userHashedPassword])[0] ?? null;
        }
    }

    public static function hash(string $password)
    {
        return hash('sha256', $password);
    }

}
