<?php

namespace Fram\Routing;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Represents a route who matched a request.
 */
class Route
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $handler;

    /**
     * @var array
     */
    private $matchedParams;

    /**
     * @var bool
     */
    private $isFailure;

    /**
     * Constructor.
     *
     * @param string $name The name of the Route.
     * @param mixed $handler The action to handle the request.
     * @param array $matchedParams The parameters extracted from the pattern
     * @param bool $isFailure
     */
    public function __construct(string $name, ?string $handler, array $matchedParams, bool $isFailure)
    {
        $this->name = $name;
        $this->handler = $handler;
        $this->matchedParams = $matchedParams;
        $this->isFailure = $isFailure;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getHandler()
    {
        return $this->handler;
    }

    public function getMatchedParams(): array
    {
        return $this->matchedParams;
    }

    public function isFailure(): bool
    {
        return $this->isFailure;
    }
}
