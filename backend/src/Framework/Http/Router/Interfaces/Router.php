<?php

namespace Framework\Http\Router\Interfaces;


use Framework\Http\Actions\PageNotFound;
use Psr\Http\Message\RequestInterface;

abstract class Router
{
    protected RequestInterface $request;
    protected string $default = PageNotFound::class;

    public function __construct(RequestInterface $request)
    {
        $this->request = $request;
    }

    abstract public function resolve();
}
