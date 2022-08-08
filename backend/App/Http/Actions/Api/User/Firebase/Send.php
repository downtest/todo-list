<?php

namespace App\Http\Actions\Api\User\Firebase;

use App\Http\Interfaces\Action;
use App\Models\User;
use Framework\Services\Logger;
use Kreait\Firebase\Messaging\CloudMessage;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
// Библиотека для запросов в firebase
use Kreait\Firebase\Factory;

class Send extends Action
{
    /**
     * @throws \App\Http\Exceptions\AppException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $factory = (new Factory)
            ->withServiceAccount('/www/config/firebase.json');

//        $auth = $factory->createAuth();
//        $realtimeDatabase = $factory->createDatabase();
        $cloudMessaging = $factory->createMessaging();

        $user = User::current();

        $tokens = User\UserFirebaseToken::get('SELECT * FROM '.User\UserFirebaseToken::$table.' WHERE user_id = ?', [$user['id']]);
        $uniqueTokensArr = array_unique(array_map(fn($token) => $token['token'], $tokens));

        try {
            $result = $cloudMessaging->sendMulticast((CloudMessage::new())->withNotification([
                'title' => 'LisToDo.ru',
                'body' => 'Hi from LisTodo backend server!',
                'image' => null,
            ]), $uniqueTokensArr);

            if ($result->hasFailures()) {
                Logger::getInstance()->error("При отправке сообщений в firebase возникли ошибки!", [$result->hasFailures()[0]?->error()]);
            }

            return $this->successResponse([
                'requestData' => $request->getAttributes(),
                'result' => $result,
                'successes' => count($result->successes()),
            ]);
        } catch (\Throwable $e) {
            return $this->errorResponse([
                'requestData' => $request->getAttributes(),
                'error' => $e->getMessage(),
            ]);
        }
    }
}
