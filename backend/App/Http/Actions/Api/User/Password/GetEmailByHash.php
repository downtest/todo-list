<?php

namespace App\Http\Actions\Api\User\Password;


use App\Http\Interfaces\Action;
use App\Models\User;
use Exception;
use Framework\Services\DBPostgres;
use Framework\Services\Lang;
use Framework\Services\Mailer;
use Framework\Services\Templater;
use Laminas\Diactoros\ServerRequest;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Laminas\Diactoros\Response\JsonResponse;

class GetEmailByHash extends Action
{
    public function validationRules($request): array
    {
        return [
            'hash' => ['required','string'],
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
        $user = $db->get('SELECT email FROM users WHERE password_change_hash = ?', [
            $request->getAttribute('hash')
        ])[0] ?? null;

        if (!$user) {
            // Юзер не найден
            return new JsonResponse([
                'status' => false,
                'errors' => ['email' => ["Не верный hash из письма"]],
            ], 422);
        }

        return $this->successResponse($user);
    }
}
