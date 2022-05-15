<?php

namespace App\Http\Actions\Api\External\Oauth;

use App\Http\BusinessServices\Login;
use App\Http\BusinessServices\Registration;
use App\Http\BusinessServices\UserContacts;
use App\Http\Interfaces\Action;
use App\Http\Resources\User\UserResource;
use App\Models\User;
use Framework\Services\Config;
use Framework\Services\Curl;
use Framework\Services\Logger;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Vk extends Action
{

    /**
     * @throws \Exception
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $loginService = Login::getInstance();

        try {
            $jsonResponse = Curl::getInstance()->post('https://oauth.vk.com/access_token', [
                'client_id' => Config::get('oauth.vk.client_id'),
                'client_secret' => Config::get('oauth.vk.client_secret'),
                'redirect_uri' => Config::get('oauth.vk.redirect_uri') . '/vk',
                'code' => $request->getAttribute('code'),
            ])->decodeJson();
        } catch (\Throwable $exception) {
            return $this->errorResponse([$exception->getMessage()]);
        }

//        $jsonResponse[access_token] => d9103c9d3557648fa7cef49e27568c52066867da118604956499a1faedaec989ed40b51513e36db6449fd
//        $jsonResponse[expires_in] => 86399
//        $jsonResponse[user_id] => 1639909
//        $jsonResponse[email] => ramirez2006@mail.ru

        Logger::getInstance()->info('access_token from vk', $jsonResponse);

        if (!empty($jsonResponse['error'])) {
            return $this->errorResponse([$jsonResponse['error'] .': '. ($jsonResponse['error_description'] ?? '')]);
        }

        // 1. Находим юзера
        if ($existedUser = User::findByEmail($jsonResponse['email'])) {
            $loginService->setUser($existedUser);

            $loginData = $loginService->getLoginData();
            $loginData['user'] = (new UserResource($existedUser))->toArray();

            return $this->successResponse($loginData);
        }


        // TODO: обработать когда email = null

        // 2. Если юзера нет, регистрируем его
        try {
            $registeredUser = Registration::getInstance()->byEmail($jsonResponse['email'], uniqid());

            UserContacts::getInstance()->setUser($registeredUser)->update([
                'email' => $jsonResponse['email'],
                'vk_id' => $jsonResponse['user_id'],
            ]);
        } catch (\Throwable $exception) {
            Logger::getInstance()->error($exception->getMessage().' IN '.$exception->getFile().':'.$exception->getLine());

            return $this->errorResponse([
                'code' => 'error-in-register',
                'common' => ["Ошибка при регистрации"],
            ]);
        }

        $loginData = $loginService->getLoginData();
        $loginData['user'] = (new UserResource($registeredUser))->toArray();

        return $this->successResponse($loginData);
    }
}
