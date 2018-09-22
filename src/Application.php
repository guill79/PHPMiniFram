<?php

namespace Fram;

use DI\ContainerBuilder;
use Fram\Middleware\MiddlewareStack;
use Fram\Renderer\RendererInterface;
use Fram\Routing\Dispatcher;
use Fram\Routing\Router;
use GuzzleHttp\Psr7\ServerRequest;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Represents the main application
 */
class Application implements RequestHandlerInterface
{
    /**
     * @var string[]
     */
    private $modules = [];

    /**
     * @var string
     */
    private $configFile;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var MiddlewareStack
     */
    private $middlewareStack;

    /**
     * Constructor.
     *
     * @param string $configFile Path to the main config file.
     */
    public function __construct(string $configFile)
    {
        $this->configFile = $configFile;
        $this->middlewareStack = new MiddlewareStack($this);
    }

    /**
     * Adds a module to the list of modules.
     *
     * @param string $module Module class name.
     * @return Application
     */
    public function addModule(string $module): self
    {
        $this->modules[] = $module;
        return $this;
    }

    /**
     * Pipe a middleware.
     *
     * If the middleware is provided as a string, it will be automatically
     * instantiated by the container.
     *
     * @param MiddlewareInterface|string $middleware
     * @return Application
     *
     * @throws Exception
     */
    public function pipe($middleware): self
    {
        if (!is_string($middleware) && !($middleware instanceof MiddlewareInterface)) {
            throw new \Exception('The middleware must be a string or an instance of MiddlewareInterface', 1);
        }

        if (is_string($middleware)) {
            $middleware = $this->getContainer()->get($middleware);
        }
        $this->middlewareStack->push($middleware);

        return $this;
    }

    /**
     * Returns the container.
     *
     * @return ContainerInterface
     */
    public function getContainer(): ContainerInterface
    {
        if ($this->container === null) {
            $builder = new ContainerBuilder();
            $builder->addDefinitions($this->configFile);
            foreach ($this->modules as $module) {
                if ($module::DEFINITIONS) {
                    $builder->addDefinitions($module::DEFINITIONS);
                }
            }
            $this->container = $builder->build();
        }
        return $this->container;
    }

    /**
     * Runs the application.
     *
     * @param ServerRequest $request The request to handle.
     * @return ResponseInterface
     */
    public function run(ServerRequestInterface $request): ResponseInterface
    {
        // Initialization of the modules
        $container = $this->getContainer();
        foreach ($this->modules as $module) {
            $container->get($module);
        }

        $this->router = $container->get(Router::class);
        $this->renderer = $container->get(RendererInterface::class);
        $this->renderer->addGlobal('router', $this->router);
        $this->renderer->addGlobal('container', $container);

        return $this->middlewareStack->handle($request);
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return (new Dispatcher($this->getContainer(), $this->router, $this->renderer))->dispatch($request);
    }
}
