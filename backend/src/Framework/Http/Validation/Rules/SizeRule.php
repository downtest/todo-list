<?php


namespace Framework\Http\Validation\Rules;


use Exception;
use Framework\Http\Validation\Rules\Interfaces\Rule;

class SizeRule extends Rule
{

    /**
     * @throws Exception
     */
    public function isValid(): bool
    {
        if (!$this->value) {
            return false;
        }

        $size = (int)$this->arguments[0];

        switch (gettype($this->value)) {
            case 'integer':
                return strlen((string)$this->value) === $size;
            case 'string':
                return mb_strlen($this->value) === $size;
            case 'array':
                return count($this->value) === $size;
            default:
                throw new Exception("Правило Size не применимо к этому виду данных");
        }
    }
}
