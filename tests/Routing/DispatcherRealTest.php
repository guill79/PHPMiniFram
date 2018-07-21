<?php

namespace Fram\Routing;

use Fram\Renderer\RendererInterface;
use Fram\Routing\Dispatcher;
use Fram\Routing\InvalidArgumentsError;
use Fram\Routing\Router;
use DI\Container;
use GuzzleHttp\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use Tests\Routing\RouteHandler\TotoController;

class DispatcherRealTest extends TestCase
{
    private $router;
    private $container;
    private $renderer;

    public function setUp()
    {
        $this->router = new Router();
        $this->container = new Container();
        $this->renderer = $this->createMock(RendererInterface::class);
    }

    public function testControllerCallInvoke()
    {
        $this->router->get('student.show', TotoController::class, '/student');

        $request = new ServerRequest('GET', '/student');

        $dispatcher = new Dispatcher($this->container, $this->router, $this->renderer);
        $response = $dispatcher->dispatch($request);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('__invoke w/o params', (string) $response->getBody());
    }
   
    public function testControllerCallFailed()
    {
        $this->router->get('student.show', TotoController::class . '@params', '/student/{id:\d+}');

        $request = new ServerRequest('GET', '/student/3');

        $dispatcher = new Dispatcher($this->container, $this->router, $this->renderer);
        $this->expectException(InvalidArgumentsError::class);
        $dispatcher->dispatch($request);
    }
}
