<?php

namespace Tests\Middleware;

use Fram\Middleware\MiddlewareStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Tests\Middleware\DummyMiddleware;

class MiddlewareStackTest extends TestCase
{
    public function testResponseReturnedByAMiddleware()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $middleware1 = new DummyMiddleware();
        $middleware2 = $this->createMock(MiddlewareInterface::class);
        $middleware2->method('process')->willReturn(new Response(200, [], 'salut'));

        $stack = new MiddlewareStack();
        $stack->push($middleware1);
        $stack->push($middleware2);

        $response = $stack->handle($request);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('salut', (string) $response->getBody());
    }

    public function testMultipleMiddlewaresWithFinalResponse()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $middleware1 = new DummyMiddleware();
        $middleware2 = new DummyMiddleware();

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->method('handle')
            ->willReturn(new Response(404, [], 'Not Found'));

        $stack = new MiddlewareStack();
        $stack->push($middleware1);
        $stack->push($middleware2);
        $stack->addFinalHandler($handler);

        $response = $stack->handle($request);

        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals('Not Found', (string) $response->getBody());
    }
}
