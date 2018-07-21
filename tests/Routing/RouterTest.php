<?php

namespace Tests\Routing;

use Fram\Routing\Route;
use Fram\Routing\Router;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use Psr\Http\Server\MiddlewareInterface;

class RouterTest extends TestCase
{
    private $router;

    public function setUp()
    {
        $this->router = new Router();
    }

    public function testMatchSuccess()
    {
        $this->router->get('test.index', '', '/test');

        $request = new ServerRequest('GET', '/test');

        $expectedRoute = new Route(
            'test.index',
            '',
            [],
            false
        );

        $actualRoute = $this->router->match($request);
        $this->assertEquals($expectedRoute, $actualRoute);
    }

    public function testMatchSuccessWithParams()
    {
        $this->router->get('test.index', '', '/test/{id:\d+}');

        $request = new ServerRequest('GET', '/test/25');

        $expectedRoute = new Route(
            'test.index',
            '',
            ['id' => 25],
            false
        );

        $actualRoute = $this->router->match($request);
        $this->assertEquals($expectedRoute, $actualRoute);
    }

    public function testMatchFailure()
    {
        $this->router->get('test.index', '', '/test');

        $request = new ServerRequest('GET', '/toto');

        $expectedRoute = new Route(
            '',
            null,
            [],
            true
        );

        $actualRoute = $this->router->match($request);
        $this->assertEquals($expectedRoute, $actualRoute);
    }

    public function testGenerateUri()
    {
        $this->router->get('route', '', '/demo');
        $uri = $this->router->generateUri('route');
        $this->assertEquals('/demo', $uri);
    }

    public function testGenerateUriWithSubstitutes()
    {
        $this->router->get('demo.article', '', '/demo/{slug:[a-zA-Z-]+}-{id:\d+}');
        $uri = $this->router->generateUri('demo.article', [
            'slug' => 'mon-article',
            'id' => 42
        ]);
        $this->assertEquals('/demo/mon-article-42', $uri);
    }

    public function testGenerateUriWithSubstitutesAndParams()
    {
        $this->router->get('demo.article', '', '/demo/{slug:[a-zA-Z-]+}-{id:\d+}');
        $uri = $this->router->generateUri(
            'demo.article',
            [
                'slug' => 'mon-article',
                'id' => 42
            ],
            [
                'p' => 'salut',
                'toto' => 5
            ]
        );
        $this->assertEquals('/demo/mon-article-42?p=salut&toto=5', $uri);
    }

    public function testRedirect()
    {
        $this->router->get('redirect', '', '/redirect');
        $response = $this->router->redirect()->route('redirect');

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals(['/redirect'], $response->getHeader('Location'));
    }

    public function testRedirectWithSubstitutes()
    {
        $this->router->get('redirect', '', '/redirect/{id:\d+}');
        $response = $this->router->redirect()->route('redirect', [
            'id' => 35
        ]);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals(['/redirect/35'], $response->getHeader('Location'));
    }

    public function testRedirectDefault()
    {
        $this->router->get('redirect', '', '/redirect/{id:\d+}');
        $response = $this->router->redirect()->route('redirect', [
            'id' => 'invalid_id'
        ], [], '/');

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals(['/'], $response->getHeader('Location'));
    }

    public function testGetGroupMiddleware()
    {
        $middleware = $this->createMock(MiddlewareInterface::class);

        $this->router->get('group.index', '', '/ifjeoisj');
        $this->router->addGroupMiddleware('group', $middleware);

        $actualMiddleware = $this->router->getGroupMiddleware('group.index');
        $this->assertSame($middleware, $actualMiddleware);
    }

    public function testGetGroupMiddlewareNull()
    {
        $middleware = $this->createMock(MiddlewareInterface::class);

        $this->router->addGroupMiddleware('group', $middleware);

        $actualMiddleware = $this->router->getGroupMiddleware('invalid_route');
        $this->assertNull($actualMiddleware);
    }

    public function testGetNestedGroupMiddleware()
    {
        $middleware = $this->createMock(MiddlewareInterface::class);
        $middleware2 = $this->createMock(MiddlewareInterface::class);

        $this->router->get('group.index', '', '/ifjeoisj');
        $this->router->addGroupMiddleware('group', $middleware);
        $this->router->addGroupMiddleware('group.index', $middleware2);

        $actualMiddleware = $this->router->getGroupMiddleware('group.index');
        $this->assertSame($middleware2, $actualMiddleware);
    }
}
