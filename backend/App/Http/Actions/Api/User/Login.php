<?php

namespace App\Http\Actions\Api\User;


use App\Http\BusinessServices\Registration;
use App\Http\Interfaces\Action;
use App\Models\User;
use Exception;
use Framework\Services\DBPostgres;
use Framework\Services\Session;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Diactoros\ServerRequest;

class Login extends Action
{
    public function validationRules($request): array
    {
        return [
            'email' => ['required','string'],
            'password' => ['required','string'],
        ];
    }

    /**
     * @param ServerRequest $request
     * @return ResponseInterface
     * @throws Exception
     */
    public function handle(RequestInterface $request): ResponseInterface
    {
        $user = User::current();
        $db = DBPostgres::getInstance();

        if (!$user) {
            // Запрашиваем юзера в БД
            $user = $db->get('SELECT * FROM users WHERE email = ? AND password = ?', [
                $request->getAttribute('email'),
                Registration::hash($request->getAttribute('password'))
            ])[0] ?? null;
        }

        if (!$user) {
            // Юзер не найден
            return new JsonResponse([
                'status' => false,
                'errors' => ['email' => ["Не найден пользователь по email`у {$request->getAttribute('email')}, либо пароль не совпадает"]],
            ], 422);
        }

        // Сохранять в сессии id юзера
        Session::getInstance()->set(User::SESSION_KEY, $user['id']);

        return new JsonResponse([
            'status' => true,
            'user' => $user,
        ]);
    }
}
