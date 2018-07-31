<?php

namespace Fram\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Set the request method to match the value of the form field "_method" (DELETE or PUT).
 */
class MethodMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (array_key_exists('_method', $request->getParsedBody())
            && in_array($request->getParsedBody()['_method'], ['DELETE', 'PUT'])
        ) {
            $request = $request->withMethod($request->getParsedBody()['_method']);
        }

        return $handler->handle($request);
    }
}
