<?php


namespace App\Http;


use Framework\Http\Exceptions\ValidationException;
use Framework\Http\ExceptionsHandler as FrameworkDefaultExceptionHandler;
use Psr\Http\Message\ResponseInterface;
use Throwable;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\Response\JsonResponse;

class ExceptionsHandler extends FrameworkDefaultExceptionHandler
{
    /**
     * @param ?Throwable $exception
     * @return ResponseInterface
     */
    public function handle(?Throwable $exception): ResponseInterface
    {
        if ($exception instanceof ValidationException) {
            return new JsonResponse([
                'success' => false,
                'error' => "Exception! {$exception->getMessage()}",
            ], 422);
        }

        return new JsonResponse([
            'success' => false,
            'error' => "Exception! {$exception->getMessage()}",
        ], 500);
    }
}
