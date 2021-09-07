<?php

namespace Framework\Services;


use Framework\Tools\Arr;

class Templater
{
    public static function parse(string $name, array $arguments = [])
    {
        ob_start();

        extract($arguments);

        require self::getPathToTemplate($name);

        $buffered = ob_get_contents();

        ob_end_clean();

        return $buffered;
    }

    protected static function getPathToTemplate(string $name): ?string
    {
        $path = str_replace('.', '/', $name);
        $configPath = realpath(__DIR__ . "/../../../resources/views");
        $fullPath = "$configPath/$path.php";

        if (!file_exists($fullPath)) {
            throw new \Exception("Шаблон $fullPath не найден");
        }

        return $fullPath;
    }
}
