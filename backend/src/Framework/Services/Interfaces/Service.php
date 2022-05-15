<?php

namespace Framework\Services\Interfaces;


use Exception;

abstract class Service
{
    /**
     * НЕЛЬЗЯ ставить тип. У каждого наследника должно быть объявлено свойство instance,
     * чтобы метод getInstance static`ом ссылался бы на свойств наследника.
     * Иначе при наследовании сервис пишет в своё свойство и
     * у наследника Session может быть в instance экземпляр DBPostgres,
     * если DBPostgres::getInstance() вызван раньше
     *
     * @var static
     */
    protected static $instance;

    /**
     * @return self
     * @throws Exception
     */
    public static function getInstance(): self
    {
//        print_r('getting '.static::class. PHP_EOL);
        if (!static::$instance) {
            static::$instance = static::createInstance();
        }

        return static::$instance;
    }

    protected static function createInstance(): self
    {
//        print_r('creating '.static::class. PHP_EOL);
        return new static();
    }
}
