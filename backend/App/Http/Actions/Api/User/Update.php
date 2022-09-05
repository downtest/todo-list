<?php

namespace App\Http\Actions\Api\User;


use App\Services\User\UserContacts;
use App\Http\Interfaces\Action;
use App\Http\Resources\User\UserResource;
use App\Models\User;
use Exception;
use Framework\Services\DBPostgres;
use Framework\Services\Logger;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\ServerRequest;

class Update extends Action
{
    public function validationRules($request): array
    {
        return [
            'id' => ['required','int'],
            'name' => ['nullable','string'],
            'email' => ['nullable','email'],
            'phone' => ['nullable','string'],
            'firebaseToken' => ['nullable','string'],
        ];
    }

    /**
     * @param ServerRequest $request
     * @return ResponseInterface
     * @throws Exception
     */
    public function handle(RequestInterface $request): ResponseInterface
    {
        $phone = Register::formatPhone($request->getAttribute('phone'));
        $user = User::current();
        $db = DBPostgres::getInstance();

        if (!$user || $user['id'] !== $request->getAttribute('id')) {
            return $this->errorResponse(['Вы не можете изменять этого пользователя']);
        }

        // TODO: Изменять email

        if ($request->getAttribute('name')) {
            $db->prepare("UPDATE ".User::$table." SET 
                name=?,
                WHERE id = ?
            ", [
                $request->getAttribute('name'),
                $request->getAttribute('id'),
            ]);
        }

        UserContacts::getInstance()->setUser($user)->update([
//                'email' => $request->getAttribute('email'),
            'phone' => $phone,
            'firebase_token' => $request->getAttribute('firebaseToken'),
        ]);

        return new JsonResponse([
            'status' => true,
            'user'   => (new UserResource($request->getAttributes() + $user))->toArray(),
        ]);
    }
}
