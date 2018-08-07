<?php

namespace Fram\Routing;

use Fram\Renderer\RendererInterface;
use GuzzleHttp\Psr7\Response;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ReflectionMethod;

/**
 * Class used to dispatch the request.
 */
class Dispatcher implements RequestHandlerInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var Router
     */
    private $router;
    
    /**
     * @var string
     */
    private $routeHandler;

    /**
     * @var RendererInterface
     */
    private $renderer;

    /**
     * Constructor.
     *
     * @param ContainerInterface $container
     * @param Router $router
     * @param RendererInterface $renderer
     */
    public function __construct(
        ContainerInterface $container,
        Router $router,
        RendererInterface $renderer
    ) {
        $this->container = $container;
        $this->router = $router;
        $this->renderer = $renderer;
    }

    /**
     * Prepares the handling of the request.
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function dispatch(ServerRequestInterface $request): ResponseInterface
    {
        // Retrieve the route matching the request
        $route = $this->router->match($request);

        if ($route->isFailure()) {
            return new Response(404, [], $this->renderer->render('errors/404'));
        }
        
        // Passing route parameters to the request
        $params = $route->getMatchedParams();
        foreach ($params as $key => $value) {
            $request = $request->withAttribute($key, $value);
        }

        $this->routeHandler = $route->getHandler();

        // If the route is associated with a group middleware, we run it before
        // continuing the request.
        $groupMiddleware = $this->router->getGroupMiddleware($route->getName());
        if ($groupMiddleware) {
            return $groupMiddleware->process($request, $this);
        }

        return $this->handle($request);
    }

    /**
     * Executes the controller action associated with the request.
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if (strpos($this->routeHandler, '@') === false) {
            // No method specified
            $controllerName = $this->routeHandler;
            $method = new ReflectionMethod($this->routeHandler, '__invoke');
        } else {
            // Method specified
            $parts = explode('@', $this->routeHandler);
            $controllerName = $parts[0];
            $method = new ReflectionMethod($controllerName, $parts[1]);
        }

        $params = $this->resolveMethodParameters($request, $method);

        $controller = $this->container->get($controllerName);
        $response = $method->invokeArgs($controller, $params);

        return $this->convertResponse($response);
    }

    /**
     * Retrieves from the request the attributes required by the method.
     *
     * @param ServerRequestInterface $request
     * @param \ReflectionFunctionAbstract $method
     * @return array
     */
    private function resolveMethodParameters(
        ServerRequestInterface $request,
        \ReflectionFunctionAbstract $method
    ): array {
        $paramsToPass = [];
        $getParams = $request->getAttributes() ?? [];
        $getParams = array_merge($getParams, $request->getQueryParams() ?? []);
        $postParams = $request->getParsedBody() ?? [];
        $postParams = array_merge($postParams, $request->getUploadedFiles());
        foreach ($method->getParameters() as $param) {
            $name = $param->getName();
            $type = $param->getType();
            if (array_key_exists($name, $getParams)) {
                // If requested param exists in GET params
                $paramsToPass[] = $getParams[$name];
            } elseif ($type == ServerRequestInterface::class) {
                // If requested param is the request
                $paramsToPass[] = $request;
            } elseif ($type == ContainerInterface::class) {
                // If requested param is the container
                $paramsToPass[] = $this->container;
            } elseif (array_key_exists($name, $postParams)) {
                // If requested param exists in POST params
                $paramsToPass[] = $postParams[$name];
            } elseif ($param->allowsNull()) {
                // If requested param is nullable
                $paramsToPass[] = null;
            } else {
                // Otherwise we can't resolve the param
                throw new InvalidArgumentsError(
                    $method->getName(),
                    $method->getDeclaringClass()->getName(),
                    $name
                );
            }
        }
        return $paramsToPass;
    }

    /**
     * Converts the response returned by the controller to an instance of Response.
     *
     * @param string|ResponseInterface $response
     * @return ResponseInterface
     */
    private function convertResponse($response): ResponseInterface
    {
        if (is_string($response)) {
            return new Response(200, [], $response);
        } elseif ($response instanceof ResponseInterface) {
            return $response;
        } else {
            throw new \Exception('The controller action must return either a'
                . ' string or an instance of ResponseInterface', 1);
        }
    }
}
