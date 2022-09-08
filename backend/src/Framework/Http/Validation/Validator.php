<?php


namespace Framework\Http\Validation;


use Framework\Http\Exceptions\ValidationException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\Diactoros\Response\JsonResponse;

/**
 * Валидация вызывается в pipeline с Middleware`ами, поэтому наследуется от их интерфейса
 *
 * @package Framework\Http\Validation
 */
class Validator implements MiddlewareInterface
{
    protected array $validationRules;
    protected RequestHandlerInterface $action;

    public function __construct(RequestHandlerInterface $action, array $validationRules = [])
    {
        $this->validationRules = $validationRules;
        $this->action = $action;
    }

    /**
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $errors = $this->action->getValidationErrors($request);

        if ($errors) {
            return new JsonResponse([
                'status' => false,
                'error' => "From validator",
                'errors' => $errors,
            ], 422);
        }

        return $handler->handle($request);
    }

    /**
     * @param array $rules
     * @param $field
     * @return array
     * @throws ValidationException
     */
    public static function getValidationErrors(array $rules, $field): array
    {
        $errors = [];

        foreach ($rules as $rule) {
            if (is_callable($rule)) {
                $result = $rule($field);
            } else {
                $ruleParts = explode(':', $rule);
                $ruleName = ucfirst(array_shift($ruleParts)) . 'Rule';
                $arguments = explode(',', $ruleParts ? $ruleParts[0] : '');
                $ruleClassName = "\Framework\Http\Validation\Rules\\{$ruleName}";

                if (!class_exists($ruleClassName)) {
                    throw new ValidationException("Нет правила валидации {$rule}");
                }

                $result = (new $ruleClassName($field, $arguments))->isValid();
            }

            if (!$result) {
                $errors[] = "Не прошла проверка правилом {$rule}";
            }
        }

        return $errors;
    }
}
