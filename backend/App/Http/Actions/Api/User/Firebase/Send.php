<?php

namespace App\Http\Actions\Api\User\Firebase;

use App\Http\Interfaces\Action;
use App\Models\User;
use App\Services\FireBase;
use Framework\Services\Config;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;


class Send extends Action
{
    /**
     * @throws \App\Http\Exceptions\AppException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $user = User::current();

        $tokens = User\UserFirebaseToken::get('SELECT * FROM '.User\UserFirebaseToken::$table.' WHERE user_id = ?', [$user['id']]);
        $uniqueTokensArr = array_unique(array_map(fn($token) => $token['token'], $tokens));

        try {
            $result = FireBase::getInstance()->sendMulticast(
                'LisToDo.ru',
                'Hi from LisTodo backend server!',
                $uniqueTokensArr,
                Config::get('app.host').'/list'
            );

            return $this->successResponse([
                'requestData' => $request->getAttributes(),
                'successes' => $result,
            ]);
        } catch (\Throwable $e) {
            return $this->errorResponse([
                'requestData' => $request->getAttributes(),
                'errors' => [$e->getMessage()],
            ]);
        }
    }
}
