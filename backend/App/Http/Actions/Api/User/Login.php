<?php

namespace App\Http\Actions\Api\User;


use App\Http\BusinessServices\Registration;
use App\Http\Interfaces\Action;
use App\Http\Resources\User\UserResource;
use App\Models\User;
use App\Models\UserToken;
use Exception;
use Framework\Services\DBPostgres;
use Framework\Services\Headers;
use Framework\Services\Session;
use Framework\Tools\Str;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\ServerRequest;

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

        // Создаём токен
        if ($tokens = $db->get('SELECT * 
            FROM user_tokens 
            WHERE (expire_at < CURRENT_TIMESTAMP OR expire_at IS NULL)
              AND user_id = ?',
            [$user['id']]
        )) {
            // Токены есть
            $token = $tokens[0];
        } else {
            $token = UserToken::create([
                'token' => uniqid(),
                'user_id' => $user['id'],
                'device_header' => Session::getInstance()->get('User-Agent'),
                'expire_at' => null,
            ]);
        }

        return new JsonResponse([
            'status' => true,
            'user' => (new UserResource($user))->toArray(),
            'token' => $token['token'],
        ]);
    }
}
