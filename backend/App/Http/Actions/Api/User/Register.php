<?php

namespace App\Http\Actions\Api\User;


use App\Http\BusinessServices\Registration;
use App\Http\Interfaces\Action;
use App\Http\Resources\User\UserResource;
use App\Models\User;
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

        $getExistedUser = $service->setEmail($request->getAttribute('email', ''))->getExistedUser();

        if ($getExistedUser) {
            if (Registration::hash($getExistedUser['password']) !== Registration::hash($request->getAttribute('password', ''))) {
                return $this->errorResponse([
                    'code' => 'email-exists',
                    'common' => ["Пользователь {$getExistedUser['email']} уже существует"],
                ]);
            } else {
                // Логинимся
                Session::getInstance()->set(User::SESSION_KEY, $getExistedUser['id']);

                return $this->successResponse([
                    'status' => true,
                    'user' => $getExistedUser,
                ]);
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

        // Логинимся
        Session::getInstance()->set(User::SESSION_KEY, $registeredUser['id']);

        return new JsonResponse([
            'status' => true,
            'user' => (new UserResource($registeredUser))->toArray(),
        ]);
    }

    public static function formatPhone(string $phone): string
    {
        return '+'.str_replace(['+','(',')',' ','-'], '', $phone);
    }
}
