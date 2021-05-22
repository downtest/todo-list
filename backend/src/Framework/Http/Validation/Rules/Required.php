<?php


namespace Framework\Http\Validation\Rules;


use Framework\Http\Validation\Rules\Interfaces\Rule;

class Required extends Rule
{

    public function isValid(): bool
    {
        return (bool)$this->value;
    }
}
