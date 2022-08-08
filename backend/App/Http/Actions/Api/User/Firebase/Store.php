<?php

namespace App\Http\Actions\Api\User\Firebase;

use App\Http\Interfaces\Action;
use App\Models\User;
use Framework\Services\Headers;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Store extends Action
{

    public function validationRules($request): array
    {
        return [
            'userId' => ['required','int'],
            'firebaseToken' => ['required','string'],
        ];
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $firebaseToken = User\UserFirebaseToken::create([[
            'user_id' => $request->getAttribute('userId'),
            'token' => $request->getAttribute('firebaseToken'),
            'device_hash' => md5(Headers::getInstance()->get('User-Agent')),
            'device' => Headers::getInstance()->get('User-Agent'),
        ]], ['token', 'device_hash']);

        return $this->successResponse([
            'firebaseToken' => $firebaseToken,
        ]);
    }
}
