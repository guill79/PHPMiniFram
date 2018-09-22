<?php

namespace Fram\Routing;

use Fram\Middleware\MiddlewareStack;
use Fram\Response\RedirectResponse;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Zend\Expressive\Router\Exception\RuntimeException;
use Zend\Expressive\Router\FastRouteRouter;
use Zend\Expressive\Router\Route as ZendRoute;
use Zend\Expressive\Router\RouteResult;

/**
 * Stores routes.
 */
class Router
{
    /**
     * @var FastRouteRouter
     */
    private $internalRouter;

    /**
     * [
     *     'group_name' => [
     *          $middleware,
     *          ...
     *      ],
     *      ...
     * ]
     * @var array
     */
    private $groupMiddlewares = [];

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->internalRouter = new FastRouteRouter();
    }

    /**
     * Adds a GET route.
     *
     * @param string $name Name of the route.
     * @param string $handler Handler.
     * @param string $path URL.
     */
    public function get(string $name, string $handler, string $path): void
    {
        $this->internalRouter->addRoute(new ZendRoute($path, $handler, ['GET'], $name));
    }

    /**
     * Adds a POST route.
     *
     * @param string $name Name of the route.
     * @param string $handler Handler.
     * @param string $path URL.
     */
    public function post(string $name, string $handler, string $path): void
    {
        $this->internalRouter->addRoute(new ZendRoute($path, $handler, ['POST'], $name));
    }

    /**
     * Adds a DELETE route.
     *
     * @param string $name Name of the route.
     * @param string $handler Handler.
     * @param string $path URL.
     */
    public function delete(string $name, string $handler, string $path): void
    {
        $this->internalRouter->addRoute(new ZendRoute($path, $handler, ['DELETE'], $name));
    }

    /**
     * Returns the route matching the request.
     *
     * @param ServerRequestInterface $request
     * @return Route
     */
    public function match(ServerRequestInterface $request): Route
    {
        $routeResult = $this->internalRouter->match($request);
        return new Route(
            $routeResult->getMatchedRouteName(),
            $routeResult->getMatchedMiddleware(),
            $routeResult->getMatchedParams(),
            $routeResult->isFailure()
        );
    }

    /**
     * Generates the URI corresponding to the route passed in parameter.
     *
     * @param string $name The name of the route.
     * @param array $substitutes Key/value pairs to inject in the route pattern.
     * @param array $queryParams GET parameters.
     * @param string $default Default URL if route not found.
     * @return string
     */
    public function generateUri(
        string $routeName,
        array $substitutes = [],
        array $queryParams = [],
        string $default = null
    ): string {
        try {
            $uri = $this->internalRouter->generateUri($routeName, $substitutes);
        } catch (RuntimeException $e) {
            if ($default === null) {
                throw new RuntimeException('Error router generateUri');
            } else {
                return $default;
            }
        }
        if (!empty($queryParams)) {
            $uri .= '?' . http_build_query($queryParams);
        }
        return $uri;
    }

    /**
     * Adds a group middleware.
     *
     * @param string $groupName The name of the group.
     * @param MiddlewareInterface $middleware The middleware.
     */
    public function addGroupMiddleware(string $groupName, MiddlewareInterface... $middlewares)
    {
        foreach ($middlewares as $middleware) {
            $this->groupMiddlewares[$groupName][] = $middleware;
        }
    }

    /**
     * Returns the middleware stack corresponding to the group of the route.
     *
     * @param string $routeName The name of the route.
     * @return MiddlewareStack
     */
    public function getMiddlewareStack(string $routeName): MiddlewareStack
    {
        $groupName = '';
        $parts = explode('.', $routeName);
        $stack = new MiddlewareStack();

        foreach ($parts as $key => $value) {
            if ($key > 0) {
                $groupName .= '.';
            }
            $groupName .= $value;
            if (isset($this->groupMiddlewares[$groupName])) {
                foreach ($this->groupMiddlewares[$groupName] as $middleware) {
                    $stack->push($middleware);
                }
            }
        }

        return $stack;
    }

    /**
     * Returns a RedirectResponse used to redirect.
     * @param int $status The status code
     * @return RedirectResponse
     */
    public function redirect(int $status = 302): RedirectResponse
    {
        return new RedirectResponse($this, $status);
    }
}
