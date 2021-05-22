<?php

namespace App\Http\Actions\Api\User;


use App\Http\Interfaces\Action;
use App\Models\User;
use Framework\Services\Session;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Diactoros\ServerRequest;

class Login extends Action
{
    public function validationRules($request): array
    {
        return ['phone' => ['required','size:8']];
    }

    /**
     * @param ServerRequest $request
     * @return ResponseInterface
     * @throws \Exception
     */
    public function handle(RequestInterface $request): ResponseInterface
    {
        $phone = Register::formatPhone($request->getAttribute('phone'));
        $user = User::current();
        // Сохранять в сессии id юзера

        if (!$user) {
            // Запрашиваем юзера в БД
            $user = User::query("SELECT * FROM users WHERE phone = '{$phone}'")[0] ?? null;
        }

        if (!$user) {
            // Юзер не найден
            return new JsonResponse([
                'status' => false,
                'message' => "Не найден пользователь по телефону {$request->getAttribute('phone')}",
            ], 422);
        }

        Session::getInstance()->set(User::SESSION_KEY, $user['id']);

        return new JsonResponse([
            'status' => true,
            'user' => $user,
        ]);
    }
}
