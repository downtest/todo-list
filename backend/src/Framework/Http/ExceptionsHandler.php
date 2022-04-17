<?php

namespace Framework\Http;


use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;
use Laminas\Diactoros\Response\HtmlResponse;

class ExceptionsHandler
{
    /**
     * @var ?RequestInterface
     */
    protected ?RequestInterface $request;

    public function __construct(?RequestInterface $request = null)
    {
        $this->request = $request;
    }

    /**
     * @param Throwable $exception
     * @return ResponseInterface
     */
    public function handle(Throwable $exception): ResponseInterface
    {
        return new HtmlResponse("Exception! {$exception->getMessage()} in {$exception->getFile()} ON LINE: {$exception->getLine()}", 500);
    }
}
