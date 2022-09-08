<?php

namespace App\Http\Actions\Api\User\Firebase;

use App\Http\Interfaces\Action;
use App\Models\User;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Get extends Action
{

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if (!$user = User::current()) {
            return $this->successResponse([
                'tokens' => [],
            ]);
        }

        $tokens = User\UserFirebaseToken::get('SELECT * FROM '.User\UserFirebaseToken::$table.' WHERE user_id = ?', [$user['id']]);

        return $this->successResponse([
            'tokens' => $tokens,
        ]);
    }
}
