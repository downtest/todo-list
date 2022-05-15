<?php

namespace App\Http\Actions\Api\User;


use App\Http\BusinessServices\Registration;
use App\Http\Interfaces\Action;
use App\Http\Resources\User\UserResource;
use App\Models\Collection;
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
        $service = \App\Http\BusinessServices\Login::getInstance();

        $result = $service
            ->byEmail(
                $request->getAttribute('email'),
                $request->getAttribute('password')
            );

        if ($result['status']) {
            $result['user'] = (new UserResource($result['user']))->toArray();

            return $this->successResponse($result);
        } else {
            // Юзер не найден
            return $this->errorResponse($result, 422);
        }
    }
}
