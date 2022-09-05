<?php

namespace App\Http\Actions\Api\User;


use App\Services\Registration;
use App\Http\Interfaces\Action;
use App\Http\Resources\User\UserResource;
use App\Models\User;
use App\Models\UserToken;
use Exception;
use Framework\Services\Logger;
use Framework\Services\Session;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\ServerRequest;

class Register extends Action
{
    public function validationRules($request): array
    {
        return [
            'email' => ['required','email'],
            'password' => ['required','string'],
            'phone' => ['nullable','string'],
            'name' => ['nullable','string'],
        ];
    }

    /**
     * @param ServerRequest $request
     * @return ResponseInterface
     * @throws Exception
     */
    public function handle(RequestInterface $request): ResponseInterface
    {
        $service = Registration::getInstance();
        $loginService = \App\Services\Login::getInstance();

        $getExistedUser = $service->setEmail($request->getAttribute('email', ''))->getExistedUser();

        if ($getExistedUser) {
            if (Registration::hash($getExistedUser['password']) !== Registration::hash($request->getAttribute('password', ''))) {
                return $this->errorResponse([
                    'code' => 'email-exists',
                    'common' => ["Пользователь {$request->getAttribute('email', '')} уже существует"],
                ]);
            } else {
                // Логинимся
                $loginServiceResult = $loginService->setUser($getExistedUser)->getLoginData();
                $loginServiceResult['user'] = (new UserResource($loginServiceResult['user']))->toArray();

                return $this->successResponse($loginServiceResult);
            }
        }

        try {
            $registeredUser = $service->byEmail($request->getAttribute('email', ''), $request->getAttribute('password'));
        } catch (\Throwable $exception) {
            Logger::getInstance()->error($exception->getMessage().' IN '.$exception->getFile().':'.$exception->getLine());

            return $this->errorResponse([
                'code' => 'error-in-register',
                'common' => ["Ошибка при регистрации"],
            ]);
        }

        $loginServiceResult = $loginService->setUser($registeredUser)->getLoginData();
        $loginServiceResult['user'] = (new UserResource($registeredUser))->toArray();

        return $this->successResponse($loginServiceResult);
    }

    public static function formatPhone(?string $phone = null): ?string
    {
        if (!$phone){
            return null;
        }

        return '+'.str_replace(['+','(',')',' ','-'], '', $phone);
    }
}
