<?php

namespace Fram;

use Fram\Auth\Auth;
use Fram\Renderer\RendererInterface;
use Fram\RequestHandler;
use Fram\Routing\Dispatcher;
use Fram\Routing\Router;
use Fram\Session\FlashMessage;
use DI\ContainerBuilder;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Represents the main application
 */
class Application
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
     * Constructor.
     *
     * @param string $configFile Path to the main config file.
     */
    public function __construct(string $configFile)
    {
        $this->configFile = $configFile;
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
     * TODO : add support for middlewares (the injection of the IP address, etc,
     * have nothing to do here).
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

        // Inject IP address in the request
        $request = $request->withAttribute('ipAddress', $_SERVER['REMOTE_ADDR']);

        // Check if $_POST['_method'] is defined
        if (array_key_exists('_method', $request->getParsedBody())
            && in_array($request->getParsedBody()['_method'], ['DELETE', 'PUT'])
        ) {
            $request = $request->withMethod($request->getParsedBody()['_method']);
        }

        $router = $container->get(Router::class);
        $renderer = $container->get(RendererInterface::class);
        $renderer->addGlobal('router', $router);
        $renderer->addGlobal('container', $container);

        // Retrieve URI
        $uri = $request->getUri()->getPath();
        if (!empty($uri) && $uri[-1] === '/' && $uri !== '/') {
            return $router->redirect(301)->uri(substr($uri, 0, -1));
        }

        return (new Dispatcher($container, $router, $renderer))->dispatch($request);
    }
}
