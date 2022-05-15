<?php


namespace App\Http\BusinessServices\User;

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
     * @throws \Exception
     */
    public function update(array $contacts)
    {
        if (!$this->user) {
            throw new \Exception('Нельзя сохранять контакты до задания пользователя методом setUser()');
        }

        if (!$this->libContacts) {
            $this->loadLibContacts();
        }

        $dataToInsert = [];

        foreach ($contacts as $contactName => $value) {
            if (empty($this->libContacts[$contactName])) {
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

}
