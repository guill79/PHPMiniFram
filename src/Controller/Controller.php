<?php

namespace Fram\Controller;

use Fram\Database\Manager;
use Fram\Renderer\RendererInterface;
use Fram\Routing\Router;
use Fram\Validator\Validator;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Base class for application controllers. Although it provides additional methods,
 * the app controllers doesn't have to extends it necessarily.
 */
abstract class Controller
{
    /**
     * @var RendererInterface
     */
    protected $renderer;

    /**
     * @var Router
     */
    protected $router;

    /**
     * Constructor.
     *
     * @param RendererInterface $renderer
     * @param Router $router
     */
    public function __construct(RendererInterface $renderer, Router $router)
    {
        $this->renderer = $renderer;
        $this->router = $router;
    }

    /**
     * Returns a new Validator filled with the POST parameters fetched from
     * the request.
     *
     * This method should be overriden in children to get a more precise
     * validator, according to the needs of the child.
     *
     * @param ServerRequestInterface $request
     * @return Validator
     * @deprecated
     */
    protected function getValidator(ServerRequestInterface $request)
    {
        return new Validator($this->getParams($request));
    }

    /**
     * Returns a new entity corresponding to the type of entity managed by the
     * controller.
     *
     * This function should be overriden in children to get a specific entity
     * and not an stdClass.
     *
     * @return stdClass
     */
    protected function getNewEntity()
    {
        return new \stdClass();
    }
}
