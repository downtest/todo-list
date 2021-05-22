<?php

namespace Framework\Http\Middleware\Interfaces;


use Framework\Http\Middleware\Next;
use Framework\Services\Config;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

abstract class Pipeline
{
    protected array $pipes = [];

    public function pipe($pipe): self
    {
        $this->pipes[] = $pipe;

        return $this;
    }

    /**
     * PSR-15 middleware invocation.
     * Взял с laminas/laminas-stratigility
     *
     * Executes the internal pipeline, passing $handler as the "final
     * handler" in cases when the pipeline exhausts itself.
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {
        return (new Next($this->pipes, $handler))->handle($request);
    }

    public function findMiddlewaresByUri(string $uri): array
    {
        $middlewares = $this->getMiddlewares();
        $parts = explode('/', $uri);
        $iterationsCnt = count($parts) - 1;
        $patterns = [$uri, '*']; // Паттерны, по которым будем искать посредников(Middleware)
        $additionalUri = '';
        $result = [];

        for ($i = 1; $i < $iterationsCnt; $i++) {
            $additionalUri = "$additionalUri/{$parts[$i]}";
            $patterns[] = $additionalUri . '/*';
        }

        // Проходим по всем паттернам и собираем их посредников
        foreach ($patterns as $pattern) {
            if (isset($middlewares[$pattern])) {
                foreach ($middlewares[$pattern] as $middleware) {
                    if (!class_exists($middleware)) {
                        throw new \Exception("No middleware class \"$middleware\" found");
                    }

                    if (!key_exists($middleware, $result)) {
                        $result[$middleware] = new $middleware;
                    }
                }
            }
        }

        return $result;
    }

    protected function getMiddlewares(): array
    {
        return Config::get('middlewares');
    }
}
