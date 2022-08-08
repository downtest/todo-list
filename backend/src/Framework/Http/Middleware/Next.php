<?php

namespace Framework\Http\Middleware;


use Framework\Http\ExceptionsHandler as FrameworkExceptionHandler;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;

class Next implements RequestHandlerInterface
{
    /**
     * @var RequestHandlerInterface
     */
    protected RequestHandlerInterface $fallbackHandler;

    /**
     * @var array|null
     */
    protected ?array $queue = [];

    /**
     * Clones the queue provided to allow re-use.
     *
     * @param array $queue
     * @param RequestHandlerInterface $fallbackHandler Fallback handler to
     *     invoke when the queue is exhausted.
     */
    public function __construct(array $queue, RequestHandlerInterface $fallbackHandler)
    {
        $this->queue           = $queue;
        $this->fallbackHandler = $fallbackHandler;
    }

    public function handle(ServerRequestInterface $request) : ResponseInterface
    {
        if (empty($this->queue)) {
            $this->queue = null;
            return $this->fallbackHandler->handle($request);
        }

        $middleware = array_shift($this->queue);
        $next = clone $this; // deep clone is not used intentionally
        $this->queue = null; // mark queue as processed at this nesting level

        return $middleware->process($request, $next);
    }
}
