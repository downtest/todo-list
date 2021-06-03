<?php


namespace Framework\Http\Validation\Rules;


use Framework\Http\Validation\Rules\Interfaces\Rule;

class RequiredRule extends Rule
{

    public function isValid(): bool
    {
        return (bool)$this->value;
    }
}
