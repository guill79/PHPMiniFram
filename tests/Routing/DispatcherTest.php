<?php

namespace Tests\Routing;

use Fram\Renderer\RendererInterface;
use Fram\Routing\Dispatcher;
use Fram\Routing\InvalidArgumentsError;
use Fram\Routing\Route;
use Fram\Routing\Router;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Tests\Routing\RouteHandler\RouteHandler;
use Tests\Routing\RouteHandler\TotoController;

class DispatcherTest extends TestCase
{
    private $router;
    private $container;
    private $groupMiddleware;
    private $request;
    private $renderer;

    public function setUp()
    {
        $this->router = $this->createMock(Router::class);

        $this->container = $this->createMock(ContainerInterface::class);
        $this->container->method('get')->willReturn(new TotoController());
        
        $this->groupMiddleware = $this->createMock(MiddlewareInterface::class);
        $this->request = $this->createMock(ServerRequestInterface::class);
        $this->request->method('getUploadedFiles')->willReturn([]);

        $this->renderer = $this->createMock(RendererInterface::class);
    }

    private function addRouteValid($handler, array $params = [])
    {
        $this->router
            ->expects($this->once())
            ->method('match')->willReturn(new Route(
                'route.name',
                $handler,
                [],
                false
            ))
            ->with($this->isInstanceOf(ServerRequestInterface::class));
    }

    private function addRouteFailed()
    {
        $this->router
            ->expects($this->once())
            ->method('match')->willReturn(new Route(
                '',
                null,
                [],
                true
            ))
            ->with($this->isInstanceOf(ServerRequestInterface::class));
    }

    private function addGroupMiddleware($returnValue)
    {
        $this->router
            ->expects($this->once())
            ->method('getGroupMiddleware')->willReturn($returnValue)
            ->with($this->isType('string'));
    }

    public function testDispatchingWithoutGroupMiddleware()
    {
        $this->addGroupMiddleware(null);

        $this->addRouteValid(TotoController::class);

        $dispatcher = new Dispatcher($this->container, $this->router, $this->renderer);
        $response = $dispatcher->dispatch($this->request);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('__invoke w/o params', (string) $response->getBody());
    }

    public function testDispatchingWithGroupMiddlewareBlocking()
    {
        $this->addGroupMiddleware($this->groupMiddleware);

        $this->groupMiddleware
            ->expects($this->once())
            ->method('process')->willReturn(new Response(403, [], 'Stopped by Middleware'))
            ->with($this->isInstanceOf(ServerRequestInterface::class), $this->isInstanceOf(RequestHandlerInterface::class));

        $this->addRouteValid(TotoController::class);

        $dispatcher = new Dispatcher($this->container, $this->router, $this->renderer);
        $response = $dispatcher->dispatch($this->request);

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertEquals('Stopped by Middleware', (string) $response->getBody());
    }

    public function testDispatchingWithGroupMiddlewareDoingNothing()
    {
        $this->addGroupMiddleware($this->groupMiddleware);

        $dispatcher = new Dispatcher($this->container, $this->router, $this->renderer);

        $this->groupMiddleware
            ->expects($this->once())
            ->method('process')->will($this->returnCallback([$dispatcher, 'handle']))
            ->with($this->isInstanceOf(ServerRequestInterface::class), $this->isInstanceOf(RequestHandlerInterface::class));

        $this->addRouteValid(TotoController::class);

        $response = $dispatcher->dispatch($this->request);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('__invoke w/o params', (string) $response->getBody());
    }

    public function testDispatchingWithRouteFailed()
    {
        $this->addRouteFailed();

        $dispatcher = new Dispatcher($this->container, $this->router, $this->renderer);
        $response = $dispatcher->dispatch($this->request);

        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testControllerCallWithSpecifiedMethod()
    {
        $this->addRouteValid(TotoController::class . '@salut');

        $dispatcher = new Dispatcher($this->container, $this->router, $this->renderer);
        $response = $dispatcher->dispatch($this->request);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('salut w/o params', (string) $response->getBody());
    }

    public function testControllerCallWithSpecifiedMethodWithParams()
    {
        $routeSubstitutes = [
            'slug' => 'titi',
            'osef' => 'ghg',
            'id' => '6'
        ];
        $this->addRouteValid(TotoController::class . '@params', $routeSubstitutes);
        $this->request->method('getAttributes')->willReturn($routeSubstitutes);

        $dispatcher = new Dispatcher($this->container, $this->router, $this->renderer);
        $response = $dispatcher->dispatch($this->request);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('params : titi, 6', (string) $response->getBody());
    }

    public function testControllerCallWithSpecifiedMethodWithParamsVariant1()
    {
        $routeSubstitutes = [
            'slug' => 'tata'
        ];
        $this->addRouteValid(TotoController::class . '@tutu', $routeSubstitutes);
        $this->request->method('getAttributes')->willReturn($routeSubstitutes);

        $dispatcher = new Dispatcher($this->container, $this->router, $this->renderer);
        $response = $dispatcher->dispatch($this->request);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('tutu tata', (string) $response->getBody());
    }

    public function testControllerCallWithSpecifiedMethodWithParamsInvalid()
    {
        $routeSubstitutes = [
            'slug' => 'tata'
        ];
        $this->addRouteValid(TotoController::class . '@invalid', $routeSubstitutes);
        $this->request->method('getAttributes')->willReturn($routeSubstitutes);

        $dispatcher = new Dispatcher($this->container, $this->router, $this->renderer);
        $this->expectException(InvalidArgumentsError::class);
        $response = $dispatcher->dispatch($this->request);
    }

    public function testControllerCallWithSpecifiedMethodWithPostParams()
    {
        $routeSubstitutes = [
            'slug' => 'titi',
            'osef' => 'ghg'
        ];
        $this->addRouteValid(TotoController::class . '@params', $routeSubstitutes);
        $this->request->method('getAttributes')->willReturn($routeSubstitutes);
        $this->request->method('getParsedBody')->willReturn([
            'id' => 4
        ]);

        $dispatcher = new Dispatcher($this->container, $this->router, $this->renderer);
        $response = $dispatcher->dispatch($this->request);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('params : titi, 4', (string) $response->getBody());
    }

    public function testControllerCallWithNullableParam()
    {
        $routeSubstitutes = [
            'slug' => 'titi'
        ];

        $this->addRouteValid(TotoController::class . '@nulo', $routeSubstitutes);
        $this->request->method('getAttributes')->willReturn($routeSubstitutes);

        $dispatcher = new Dispatcher($this->container, $this->router, $this->renderer);
        $response = $dispatcher->dispatch($this->request);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('toto titi null', (string) $response->getBody());
    }
}
