<?php

namespace App\Http\Actions\Api\User;


use App\Http\Interfaces\Action;
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
            'phone' => ['required','string'],
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

        try {
            $db->prepare("UPDATE ".User::$table." SET 
                name=?,
                email=?,
                phone=?
                WHERE id = ?
            ", [
                $request->getAttribute('name'),
                $request->getAttribute('email'),
                $phone,
                $request->getAttribute('id'),
            ]);
        } catch (\Throwable $exception) {
            Logger::getInstance()->error($exception->getMessage());

            return $this->errorResponse(["Не удалось обновить пользователя"]);
        }

        return new JsonResponse([
            'status' => true,
            'user'   => $request->getAttributes() + $user,
        ]);
    }
}
