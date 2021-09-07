<?php

namespace Framework\Services;


use Framework\Tools\Arr;

class Lang
{
    protected static array $lang = [];


    public static function get(string $name, $lang = null, $default = null)
    {
        if (!$lang) {
            $lang = Config::get('app.default_lang');
        }

        $parts = explode('.', $name);
        $phraseCode = array_pop($parts);
        $path = implode('/', $parts);

        self::load($path, $lang);

        if (empty(self::$lang[$lang][$path][$phraseCode])) {
            // Нет нужной фразы
            if ($default) {
                return $default;
            } elseif ($lang !== Config::get('app.default_lang')) {
                // Не стандартный язык, надо вернуть фразу на стандартном языке
                return self::get($name, Config::get('app.default_lang'));
            } else {
                // Такой фразы нет вообще
                return null;
            }
        }

        return self::$lang[$lang][$path][$phraseCode] ?? $default;
    }

    protected static function load(string $path, $lang): void
    {
        $configPath = realpath(__DIR__ . "/../../../resources/localization");
        $fullPath = "$configPath/$lang/$path.php";

        if (!isset(self::$lang[$lang][$path]) && file_exists($fullPath)) {
            self::$lang[$lang][$path] = require $fullPath;
        }
    }
}
