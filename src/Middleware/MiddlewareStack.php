<?php

namespace Fram\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class MiddlewareStack implements RequestHandlerInterface
{
    /**
     * @var RequestHandlerInterface
     */
    private $finalHandler;

    /**
     * @var MiddlewareInterface[]
     */
    private $middlewares = [];

    /**
     * @var int
     */
    private $middlewareIndex = 0;

    /**
     * Constructor.
     * 
     * If no final handler is specified here, make sure that you do it later !
     * 
     * @param RequestHandlerInterface $finalHandler The final handler.
     */
    public function __construct(RequestHandlerInterface $finalHandler = null)
    {
        if ($finalHandler !== null) {
            $this->addFinalHandler($finalHandler);
        }
    }

    /**
     * Specifies the final handler that will finish handling the request.
     * 
     * @param RequestHandlerInterface $finalHandler 
     */
    public function addFinalHandler(RequestHandlerInterface $finalHandler): void
    {
        $this->finalHandler = $finalHandler;
    }

    /**
     * Push the middleware onto the stack.
     * 
     * @param MiddlewareInterface $middleware 
     */
    public function push(MiddlewareInterface... $middlewares): void
    {
        foreach ($middlewares as $middleware) {
            $this->middlewares[] = $middleware;
        }
    }

    /**
     * Handle the request and return a response.
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if (!isset($this->middlewares[$this->middlewareIndex])) {
            // We reached the end of the middleware stack
            if ($this->finalHandler === null) {
                throw new \Exception("Final handler unspecified.", 1);
            }
            return $this->finalHandler->handle($request);
        }

        $middleware = $this->middlewares[$this->middlewareIndex];
        $this->middlewareIndex++;

        return $middleware->process($request, $this);
    }
}
