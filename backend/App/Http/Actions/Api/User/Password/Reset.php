<?php

namespace App\Http\Actions\Api\User\Password;


use App\Http\BusinessServices\Registration;
use App\Http\Interfaces\Action;
use App\Http\Resources\User\UserResource;
use App\Models\User;
use Exception;
use Framework\Services\DBPostgres;
use Framework\Services\Lang;
use Framework\Services\Mailer;
use Framework\Services\Session;
use Framework\Services\Templater;
use Laminas\Diactoros\ServerRequest;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Laminas\Diactoros\Response\JsonResponse;

class Reset extends Action
{
    public function validationRules($request): array
    {
        return [
            'email' => ['required','string'],
            'hash' => ['required','string'],
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
        $db = DBPostgres::getInstance();

        // Получаем юзера из БД
        $user = $db->get('SELECT * FROM '.User::$table.' 
            WHERE email = ? 
            AND password_change_hash = ?', [
                $request->getAttribute('email'),
                $request->getAttribute('hash'),
            ])[0] ?? null;

        if (!$user || empty($user['id'])) {
            // Юзер не найден
            return new JsonResponse([
                'status' => false,
                'errors' => ['common' => ["Не верный hash из письма"]],
            ], 422);
        }

        $db->prepare('UPDATE '.User::$table.' SET 
            password = ?,
            password_change_requested_at = ?,
            password_change_hash = ?
            WHERE id = ?', [
            Registration::hash($request->getAttribute('password')),
            null,
            null,
            $user['id'],
        ]);

        $user = [
            'password_change_requested_at' => null,
            'password_change_hash' => null,
        ] + $user;

        // Логинимся
        Session::getInstance()->set(User::SESSION_KEY, $user['id']);

        return $this->successResponse(['user' => (new UserResource($user))->toArray()]);
    }
}
