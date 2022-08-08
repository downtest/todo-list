<?php

namespace Framework\Http;


use Framework\Http\ExceptionsHandler as FrameworkExceptionHandler;
use Framework\Http\Middleware\Interfaces\Pipeline;
use Framework\Http\Middleware\PipelineHttp;
use Framework\Http\Requests\GlobalRequest;
use Framework\Http\Router\Interfaces\Router;
use Framework\Http\Router\RouterHttp;
use Framework\Http\Validation\Validator;
use Narrowspark\HttpEmitter\SapiEmitter;
use Psr\Http\Message\RequestInterface;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\ServerRequest;
use Laminas\Diactoros\ServerRequestFactory;

class Application
{
    /**
     * @var RequestInterface
     */
    protected RequestInterface $request;

    /**
     * @var Middleware\Interfaces\Pipeline
     */
    protected Middleware\Interfaces\Pipeline $pipeline;

    /**
     * @var Router
     */
    protected Router $router;

    /**
     * @var SapiEmitter
     */
    protected SapiEmitter $emitter;

    public function __construct(RequestInterface $request = null, Pipeline $pipeline = null, Router $router = null)
    {
        $this->request = $this->getRequestWithAttributes($request);
        $this->pipeline = $pipeline ?? new PipelineHttp();
        $this->router = $router ?? new RouterHttp($this->request);
        $this->emitter = new SapiEmitter();
    }

    public function run()
    {
        $middlewares = $this->pipeline->findMiddlewaresByUri($this->request->getUri()->getPath());

        foreach ($middlewares as $middleware) {
            $this->pipeline->pipe(new $middleware);
        }

        $action = $this->router->resolve();

        if (method_exists($action, 'validationRules') && $validationErrors = $action->validationRules($this->request)) {
            $this->pipeline->pipe(new Validator($action, $validationErrors));
        }

        $response = $this->pipeline->process($this->request, $action);

        $this->emitter->emit($response);
    }

    /**
     * @param RequestInterface|null $request
     * @return ServerRequest
     * @throws \Exception
     */
    protected function getRequestWithAttributes(RequestInterface $request = null): ServerRequest
    {
        if (!$request) {
            $request = ServerRequestFactory::fromGlobals();
        }

        foreach ($_GET as $key => $value) {
            $request = $request->withAttribute($key, $value);
        }

        if ($request->getHeader('content-type')) {
            switch (explode(';', $request->getHeader('content-type')[0])[0]) {
                case 'application/json':
                    $arr = json_decode(file_get_contents('php://input'),  true);

                    if (json_last_error()) {
                        throw new \Exception(json_last_error_msg());
                    }

                    foreach($arr as $key => $value) {
                        $request = $request->withAttribute($key, $value);
                    }

                    break;
                case 'text/plain;charset=UTF-8':
                case 'application/x-www-form-urlencoded':
                    parse_str(file_get_contents('php://input'), $arr);

                    foreach($arr as $key => $value) {
                        $request = $request->withAttribute($key, $value);
                    }

                    break;
                case 'multipart/form-data':
                    // Пока не работает, не могу распарсить запрос

                    break;
            }
        }

        return $request;
    }
}
