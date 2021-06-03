<?php


namespace Framework\Http\Validation\Rules;


use Framework\Http\Validation\Rules\Interfaces\Rule;

class NumberRule extends Rule
{

    public function isValid(): bool
    {
        if (empty($this->value)) {
            return true;
        }

        return is_numeric($this->value);
    }
}
