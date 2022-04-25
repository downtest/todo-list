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
     * @var self
     */
    protected static $instance;

    /**
     * @return static
     * @throws Exception
     */
    public static function getInstance(): static
    {
        if (!static::$instance) {
            static::$instance = static::createInstance();
        }

        return static::$instance;
    }

    protected static function createInstance(): self
    {
        return new static();
    }
}
