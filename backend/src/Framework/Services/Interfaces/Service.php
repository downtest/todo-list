<?php

namespace Framework\Services\Interfaces;


use Exception;

abstract class Service
{
    /**
     * @var self
     */
    protected static self $instance;

    /**
     * @return self
     * @throws Exception
     */
    public static function getInstance(): self
    {
        if (!static::$instance) {
            static::$instance = static::createInstance();
        } else {
        }

        return static::$instance;
    }

    protected static function createInstance(): self
    {
        return new static();
    }
}
