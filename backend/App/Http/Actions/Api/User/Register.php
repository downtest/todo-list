<?php

namespace App\Http\Actions\Api\User;


use App\Http\Interfaces\Action;
use App\Models\User;
use Exception;
use Framework\Services\Session;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Diactoros\ServerRequest;

class Register extends Action
{
    /**
     * @param ServerRequest $request
     * @return ResponseInterface
     * @throws Exception
     */
    public function handle(RequestInterface $request): ResponseInterface
    {
        $phone = static::formatPhone($request->getAttribute('phone', ''));
        $user = User::query("SELECT * FROM ".User::$table.' WHERE phone = \''.$phone.'\'');

        if (!$user) {
            $user = User::create([[
                'phone' => $phone,
            ]]);
        }

        Session::getInstance()->set(User::SESSION_KEY, $user[0]['id']);

        return new JsonResponse([
            'status' => true,
            'request' => $request->getAttribute('phone'),
            'user' => $user[0],
        ]);
    }

    public static function formatPhone(string $phone): string
    {
        return '+'.str_replace(['+','(',')',' ','-'], '', $phone);
    }
}
