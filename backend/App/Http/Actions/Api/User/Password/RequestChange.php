<?php

namespace App\Http\Actions\Api\User\Password;


use App\Http\Interfaces\Action;
use App\Models\User;
use Exception;
use Framework\Services\Config;
use Framework\Services\DBPostgres;
use Framework\Services\Lang;
use Framework\Services\Mailer;
use Framework\Services\Templater;
use Laminas\Diactoros\ServerRequest;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Laminas\Diactoros\Response\JsonResponse;

class RequestChange extends Action
{
    public function validationRules($request): array
    {
        return [
            'email' => ['required','string'],
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
        $user = $db->get('SELECT * FROM users WHERE email = ?', [
            $request->getAttribute('email')
        ])[0] ?? null;

        if (!$user) {
            // Юзер не найден
            return new JsonResponse([
                'status' => false,
                'errors' => ['email' => ["Не найден пользователь по email`у {$request->getAttribute('email')}"]],
            ], 422);
        }

        if ($user['password_change_requested_at'] && strtotime('+1 minute', strtotime($user['password_change_requested_at'])) > time()) {
            // Юзер не найден
            return new JsonResponse([
                'status' => false,
                'last' => $user['password_change_requested_at'],
                'now' => date('Y-m-d H:i-s'),
                'errors' => ['common' => ["Нельзя запрашивать смену пароля чаще, чем раз в минуту"]],
            ], 422);
        }

        // Формируем хэш для сброса пароля через email
        $hash = uniqid();

        $db->prepare("UPDATE ".User::$table." 
            SET password_change_requested_at = ?,
                password_change_hash = ?
            WHERE id = ?",
            [
                date('Y-m-d H:i:s'),
                $hash,
                $user['id'],
            ]
        );

        // Отправляем пользователю письмо для смены пароля
        $mailer = Mailer::getInstance();

        // Кому отправляем
        $mailer->addAddress($user['email'], $user['name']);
        //Content
        $mailer->subject = Lang::get('email.change_password_subject', $user['lang']);
        $mailer->html    = Templater::parse('emails.password_forget', [
            'link' => Config::get('app.host').'/password-reset?hash='.$hash,
        ]);
//        $mailer->altBody = 'Текст для клиентов, которые не поддерживают html';

        if (!$mailer->send()) {
            return $this->errorResponse(['common' => $mailer->getLastError()]);
        }

        return $this->successResponse();
    }
}
