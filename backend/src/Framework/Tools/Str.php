<?php

namespace Framework\Tools;


class Str
{
    /**
     * Преобразует snake_case или kebab-case в camelCase
     *
     * @param string $string
     * @return string|null
     */
    public static function toCamelCase(string $string): ?string
    {
        if (!is_string($string)) {
            return null;
        }

        $parts = explode('_', str_replace('-', '_', $string));
        $result = array_shift($parts);

        foreach ($parts as $part) {
            $result .= ucfirst($part);
        }

        return $result;
    }

}
