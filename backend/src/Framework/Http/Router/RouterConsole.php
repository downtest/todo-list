<?php

namespace Framework\Http\Router;


use Framework\Http\Router\Interfaces\Router;

class RouterConsole extends Router
{
    public function resolve()
    {
        global $argv;
        $className = '\App\Console\Actions\\' . ucfirst($argv[1]);

        if (!class_exists($className)) {
            return (new $this->default($this->request));
        }

       return new $className($this->request);
    }
}
