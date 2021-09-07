<?php

namespace Framework\Http\Router;


use Framework\Http\Router\Interfaces\Router;
use Framework\Tools\Arr;
use Framework\Tools\Str;

class RouterHttp extends Router
{
    public function resolve()
    {
        $className = '\App\Http\Actions';
        $parts = explode('/', trim($this->request->getUri()->getPath(), '/'));

        foreach ($parts as $part) {
            $className .= '\\' . ucfirst(Str::toCamelCase($part));
        }

        if (!class_exists($className)) {
            return (new $this->default($this->request));
        }

       return new $className($this->request);
    }
}
