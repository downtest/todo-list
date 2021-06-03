<?php


namespace Framework\Http\Validation\Rules;


use Framework\Http\Validation\Rules\Interfaces\Rule;

class NullableRule extends Rule
{

    public function isValid(): bool
    {
        return true;
    }
}
