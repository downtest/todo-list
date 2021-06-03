<?php


namespace Framework\Http\Validation\Rules;


use Framework\Http\Validation\Rules\Interfaces\Rule;

class StringRule extends Rule
{

    public function isValid(): bool
    {
        if (empty($this->value)) {
            return true;
        }

        return is_string($this->value);
    }
}
