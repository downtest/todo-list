<?php


namespace App\Services\User;

use App\Http\Exceptions\AppException;
use App\Models\User;
use Framework\Services\DBPostgres;
use Framework\Services\Interfaces\Service;

/**
 * Класс, хранящий бизнес-логику тасок
 * @package App\Http\BusinessServices
 */
class UserContacts extends Service
{
    protected static $instance;

    protected DBPostgres $db;

    protected array $user;

    protected array $libContacts = [];

    public function __construct()
    {
        $this->db = DBPostgres::getInstance();
    }

    protected function loadLibContacts(): static
    {
        $result = $this->db->get('SELECT * FROM lib_contacts');

        foreach ($result as $libContact) {
            $this->libContacts[$libContact['name']] = $libContact;
        }

        return $this;
    }

    public function setUser(array $user): static
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @param array $contacts Массив вида ['email' => 'ramirez2006@mail.ru', 'vk_id' => 12345]
     * @throws AppException
     */
    public function update(array $contacts)
    {
        if (!$this->user) {
            throw new AppException('Нельзя сохранять контакты до задания пользователя методом setUser()');
        }

        if (!$this->libContacts) {
            $this->loadLibContacts();
        }

        $dataToInsert = [];

        foreach ($contacts as $contactName => $value) {
            if (empty($this->libContacts[$contactName]) || !$value) {
                continue;
            }

            $dataToInsert[] = [
                'contact_id' => $this->libContacts[$contactName]['id'],
                'user_id' => $this->user['id'],
                'value' => $value,
            ];
        }

        // Добавляем новые, но дублей быть не должно из-за передачи 2ого параметра(ON CONFLICT ... DO UPDATE)
        User\UserContact::create($dataToInsert, ['contact_id', 'user_id', 'value']);
    }

    /**
     * @throws AppException
     */
    public function get(): array
    {
        if (!$this->user) {
            throw new AppException('Нельзя получить контакты до задания пользователя методом setUser()');
        }

        return User\UserContact::get('SELECT '.User\UserContact::$table.'.*, lib_contacts.name, lib_contacts.title 
            FROM '.User\UserContact::$table.' 
            LEFT JOIN lib_contacts ON '.User\UserContact::$table.'.contact_id = lib_contacts.id
            WHERE user_id = ?', [$this->user['id']]);
    }

}
