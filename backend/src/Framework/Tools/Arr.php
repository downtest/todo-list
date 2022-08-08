<?php

namespace Framework\Tools;


class Arr
{
    /**
     * Возвращает элемент из массива
     * Например, getByArr($arr, ['foo', 'bar']) вернёт $arr['foo']['bar']
     *
     * @param array $array $array
     * @param array $pathArr
     * @param null $default
     * @return mixed
     */
    public static function getByArr(array $array, array $pathArr, $default = null)
    {
        if (!$array) {
            return $default;
        }

        $result = $array;

        foreach ($pathArr as $part) {
            $result = $result[$part] ?? null;
        }

        return $result ?? $default;
    }

    /**
     * Получает элемент из массива по строке, разделённой точками
     * Например, getByDot($arr, 'foo.bar') вернёт $arr['foo']['bar']
     *
     * @param array $array
     * @param string $path
     * @param null $default
     * @return mixed
     */
    public static function getByDot(array $array, string $path, $default = null)
    {
        if (!$array) {
            return $default;
        }

        $pathParts = explode('.', $path);
        $result = $array;

        foreach ($pathParts as $part) {
            $result = $result[$part] ?? null;
        }

        return $result ?? $default;
    }

    /**
     * Возвращает многомерный массив без указанных ключей
     *
     * @param array|null $array $array
     * @param array $exceptValues
     * @return array
     */
    public static function except(?array $array, array $exceptValues = []): array
    {
        if (!$array) {
            return [];
        }

        return array_filter($array, function ($key) use ($exceptValues) {
            return !in_array($key, $exceptValues);
        }, ARRAY_FILTER_USE_KEY);
    }

    /**
     * Возвращает многомерный массив из указанных ключей
     *
     * @param array|null $array $array
     * @param array $onlyValues
     * @return array
     */
    public static function only(?array $array, array $onlyValues = []): array
    {
        if (!$array) {
            return [];
        }

        $result = [];

        foreach ($array as $key => $value) {
            foreach ($onlyValues as $keyName) {
                $result[$key][$keyName] = $value[$keyName];
            }
        }

        return $result;
    }
}
