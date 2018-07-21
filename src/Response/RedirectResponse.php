<?php

namespace Fram\Response;

use Fram\Routing\Router;
use GuzzleHttp\Psr7\Response;

/**
 * This class is a response containing a Location header to redirect the user.
 */
class RedirectResponse extends Response
{
    /**
     * @var Router
     */
    private $router;

    /**
     * Constructor.
     *
     * @param Router $router
     * @param int $status The status code.
     */
    public function __construct(Router $router, int $status = 302)
    {
        parent::__construct($status);
        $this->router = $router;
    }

    /**
     * Set the redirection to a route.
     *
     * @param string $routeName The name of the route.
     * @param array|array $substitutes Key/value pairs to inject in the route pattern
     * @param array $queryParams GET parameters.
     * @param string|null $default The default URI if the route doesn't exist.
     * @return RedirectResponse
     */
    public function route(
        string $routeName,
        array $substitutes = [],
        array $queryParams = [],
        ?string $default = null
    ): self {
        $uri = $this->router->generateUri($routeName, $substitutes, $queryParams, $default);
        return $this->withHeader('Location', $uri);
    }

    /**
     * Set the redirection to a URI.
     *
     * @param string $uri
     * @return RedirectResponse
     */
    public function uri(string $uri): self
    {
        return $this->withHeader('Location', $uri);
    }
}
