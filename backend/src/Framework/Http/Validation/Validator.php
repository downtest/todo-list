<?php


namespace Framework\Http\Validation;


use Framework\Http\Exceptions\ValidationException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response\JsonResponse;

/**
 * Валидация вызывается в pipeline с Middleware`ами, поэтому наследуется от их интерфейса
 *
 * @package Framework\Http\Validation
 */
class Validator implements MiddlewareInterface
{
    protected array $validationRules;

    public function __construct(array $validationRules = [])
    {
        $this->validationRules = $validationRules;
    }

    /**
     * @throws ValidationException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $errors = [];

        foreach ($this->validationRules as $field => $rules) {
            foreach ($rules as $rule) {
                if (is_callable($rule)) {
                    $result = $rule($request->getAttribute($field));
                } else {
                    $ruleParts = explode(':', $rule);
                    $ruleName = ucfirst(array_shift($ruleParts)) . 'Rule';
                    $arguments = explode(',', array_shift($ruleParts));
                    $ruleClassName = "\Framework\Http\Validation\Rules\\{$ruleName}";

                    if (!class_exists($ruleClassName)) {
                        throw new ValidationException("Нет правила валидации {$rule}");
                    }

                    $result = (new $ruleClassName($request->getAttribute($field), $arguments))->isValid();
                }

                if (!$result) {
                    $errors[$field][] = "Поле {$field} не прошло правило {$rule}";
                }
            }
        }

        if ($errors) {
            return new JsonResponse([
                'success' => false,
                'error' => "From validator",
                'errors' => $errors,
            ], 422);
        }

        return $handler->handle($request);
    }
}
