<?php


namespace App\Http\Interfaces;


use Framework\Http\Exceptions\ValidationException;
use Framework\Http\Validation\Validator;
use Framework\Tools\Arr;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response\JsonResponse;

abstract class Action implements RequestHandlerInterface
{

    abstract public function handle(ServerRequestInterface $request): ResponseInterface;

    public function validationRules(ServerRequestInterface $request): array
    {
        return [];
    }

    /**
     * @throws ValidationException
     */
    public function getValidationErrors(ServerRequestInterface $request): array
    {
        $errors = [];

        foreach ($this->validationRules($request) as $fieldPath => $rules) {
            // TODO: field can be :
            // updated
            // updated.* // Если есть *, то
            // updated.*.id
            // updated.id
            $field = Arr::getByDot($request->getAttributes(), $fieldPath);

            if ($foundErrors = Validator::getValidationErrors($rules, $field)) {
                $errors[$fieldPath] = $foundErrors;
            }
        }

        return $errors;
    }

    public function successResponse(array $data = []): JsonResponse
    {
        return new JsonResponse(['success' => true] + $data);
    }

    public function errorResponse(array $data = []): JsonResponse
    {
        return new JsonResponse(['success' => false, 'errors' => $data], 400);
    }

    public function validationErrorResponse(array $errors = []): JsonResponse
    {
        return new JsonResponse(['success' => false, 'errors' => $errors], 422);
    }
}
