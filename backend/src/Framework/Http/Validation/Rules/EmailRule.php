<?php


namespace Framework\Http\Validation\Rules;


use Exception;
use Framework\Http\Validation\Rules\Interfaces\Rule;

class EmailRule extends Rule
{

    /**
     * @throws Exception
     */
    public function isValid(): bool
    {
        if (!$this->value) {
            return true;
        }

        return preg_match('/.*@.*\..*/', $this->value);
    }
}
