<?php


namespace App\Http;


use Framework\Http\Exceptions\ValidationException;
use Framework\Http\ExceptionsHandler as FrameworkDefaultExceptionHandler;
use Framework\Services\Logger;
use Psr\Http\Message\ResponseInterface;
use Throwable;
use Laminas\Diactoros\Response\JsonResponse;

class ExceptionsHandler extends FrameworkDefaultExceptionHandler
{
    /**
     * @param ?Throwable $exception
     * @return ResponseInterface
     */
    public function handle(?Throwable $exception): ResponseInterface
    {
        Logger::getInstance()->error("Exception! {$exception->getMessage()} in {$exception->getFile()} ON LINE: {$exception->getLine()}\n" . print_r($exception->getTrace(), true));

        if ($exception instanceof ValidationException) {
            return new JsonResponse([
                'status' => false,
                'error' => "Exception! {$exception->getMessage()} in {$exception->getFile()} ON LINE: {$exception->getLine()}",
            ], 422);
        }

        if ($exception instanceof \PDOException) {
//            var_dump($exception);
            return new JsonResponse([
                'status' => false,
                'error' => "Exception! {$exception->getMessage()} in {$exception->getFile()} ON LINE: {$exception->getLine()}",
                'trace' => $exception->getTraceAsString()
            ], 422);
        }

        return new JsonResponse([
            'status' => false,
            'error' => "Exception! {$exception->getMessage()} in {$exception->getFile()} ON LINE: {$exception->getLine()}",
        ], 500);
    }
}
