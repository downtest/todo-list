<?php


namespace App\Http;


use App\Http\Exceptions\AppException;
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
        $context = [];

        if ($lastStep = $exception->getTrace()[0]) {
            $context['file'] = $lastStep['file'] .':'. $lastStep['line'];
        }

        if ($exception instanceof AppException) {
            $context += $exception->getContext();

            Logger::getInstance()->error("Exception! {$exception->getMessage()} in {$exception->getFile()} ON LINE: {$exception->getLine()}\n", $context);

            return new JsonResponse([
                'status' => false,
                'errors' => ["Exception! {$exception->getMessage()} in {$exception->getFile()} ON LINE: {$exception->getLine()}"],
            ], 422);
        }


        Logger::getInstance()->error("Exception! {$exception->getMessage()} in {$exception->getFile()} ON LINE: {$exception->getLine()}\n", $context);

        if ($exception instanceof ValidationException) {
            return new JsonResponse([
                'status' => false,
                'errors' => ["Exception! {$exception->getMessage()} in {$exception->getFile()} ON LINE: {$exception->getLine()}"],
            ], 422);
        }

        if ($exception instanceof \PDOException) {
//            var_dump($exception);
            return new JsonResponse([
                'status' => false,
                'errors' => ["Exception! {$exception->getMessage()} in {$exception->getFile()} ON LINE: {$exception->getLine()}"],
                'trace' => $exception->getTraceAsString()
            ], 422);
        }

        return new JsonResponse([
            'status' => false,
            'error' => "Exception! {$exception->getMessage()} in {$exception->getFile()} ON LINE: {$exception->getLine()}",
        ], 500);
    }
}
