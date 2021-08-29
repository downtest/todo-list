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

class Logout extends Action
{
    /**
     * @param ServerRequest $request
     * @return ResponseInterface
     * @throws Exception
     */
    public function handle(RequestInterface $request): ResponseInterface
    {
        Session::getInstance()->unset(User::SESSION_KEY);

        return new JsonResponse([
            'status' => true,
        ]);
    }
}
