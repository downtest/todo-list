<?php


namespace App\Http;


use Framework\Http\Exceptions\ValidationException;
use Framework\Http\ExceptionsHandler as FrameworkDefaultExceptionHandler;
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
        if ($exception instanceof ValidationException) {
            return new JsonResponse([
                'status' => false,
                'error' => "Exception! {$exception->getMessage()}",
            ], 422);
        }

        if ($exception instanceof \PDOException) {
//            var_dump($exception);
            return new JsonResponse([
                'status' => false,
                'error' => "Exception! {$exception->getMessage()}",
                'trace' => $exception->getTraceAsString()
            ], 422);
        }

        return new JsonResponse([
            'status' => false,
            'error' => "Exception! {$exception->getMessage()}",
        ], 500);
    }
}
