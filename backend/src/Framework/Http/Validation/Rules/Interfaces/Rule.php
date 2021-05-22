<?php


namespace Framework\Http\Validation\Rules\Interfaces;


abstract class Rule
{
    /**
     * @var mixed
     */
    protected $value;

    /**
     * @var array
     */
    protected array $arguments;

    public function __construct($value, array $arguments = [])
    {
        $this->value = $value;
        $this->arguments = $arguments;

        return $this;
    }

    /**
     * Должен вернуть булево значение
     *
     * @return bool
     */
    abstract public function isValid(): bool;
}
