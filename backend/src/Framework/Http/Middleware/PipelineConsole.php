<?php

namespace Framework\Http\Middleware;


use Framework\Http\Middleware\Interfaces\Pipeline;
use Framework\Services\Config;

class PipelineConsole extends Pipeline
{
    protected function getMiddlewares(): array
    {
        return Config::get('middlewares', []);
    }
}
