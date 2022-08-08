<?php

require '../vendor/autoload.php';

$request = \Laminas\Diactoros\ServerRequestFactory::fromGlobals();

(new \Framework\Http\Application(
    $request,
    new \Framework\Http\Middleware\PipelineHttp(),
    new \Framework\Http\Router\RouterHttp($request)
))->run();
