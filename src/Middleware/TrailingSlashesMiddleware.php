<?php

namespace Fram\Middleware;

use Fram\Response\RedirectResponse;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Remove the trailing slashes on the URL.
 */
class TrailingSlashesMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $uri = $request->getUri()->getPath();
        if (!empty($uri) && $uri[-1] === '/' && $uri !== '/') {
            return (new RedirectResponse(null, 301))->uri(substr($uri, 0, -1));
        }

        return $handler->handle($request);
    }
}
