<?php

namespace Framework\Services;


use Framework\Tools\Arr;

class Config
{
    protected static array $config = [];


    public static function get(string $name, $default = null)
    {
        $parts = explode('.', $name);
        $configName = array_shift($parts);

        self::load($configName);

        if (!isset(self::$config[$configName])) {
            return $default;
        }

        return Arr::getByArr(self::$config[$configName], $parts, $default);
    }

    public static function load(string $name): void
    {
        $configPath = realpath(__DIR__."/../../../App/config");
        $fullPath = "{$configPath}/{$name}.php";

        if (!isset(self::$config[$name]) && file_exists($fullPath)) {
            self::$config[$name] = require $fullPath;
        }
    }
}
