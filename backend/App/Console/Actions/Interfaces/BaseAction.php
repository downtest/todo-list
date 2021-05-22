<?php

namespace App\Console\Actions\Interfaces;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

abstract class BaseAction implements RequestHandlerInterface
{
    abstract public function handle(ServerRequestInterface $request): ResponseInterface;
}
