<?php

namespace App\Http\Actions\Api\User\Firebase;

use App\Http\Exceptions\AppException;
use App\Http\Interfaces\Action;
use App\Models\User;
use Framework\Services\DBPostgres;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
// Библиотека для запросов в firebase
use Kreait\Firebase\Factory;

class Delete extends Action
{
    /**
     * @throws \App\Http\Exceptions\AppException
     * @throws \Exception
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $user = User::current();

        if ($user['id'] !== $request->getAttribute('userId')) {
            throw new AppException("Попытка удалить токен другого пользователя");
        }

        User\UserFirebaseToken::query('DELETE FROM '.User\UserFirebaseToken::$table.' WHERE user_id = '.(int)$request->getAttribute('userId').' AND token = '.DBPostgres::getInstance()->quote($request->getAttribute('token')));

        return $this->successResponse();
    }
}
