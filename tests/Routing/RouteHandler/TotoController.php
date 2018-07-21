<?php

namespace Tests\Routing\RouteHandler;

use Psr\Http\Message\ServerRequestInterface;

class TotoController
{
    public function __invoke()
    {
        return '__invoke w/o params';
    }

    public function salut()
    {
        return 'salut w/o params';
    }

    public function params(string $slug, int $id)
    {
        return 'params : ' . $slug . ', ' . $id;
    }

    public function tutu(ServerRequestInterface $request, string $slug)
    {
        return 'tutu ' . $slug;
    }

    public function invalid(string $slug, int $id)
    {
        return 'toto';
    }

    public function nulo(string $slug, ?int $id)
    {
        return 'toto ' . $slug . ' ' . ($id === null ? 'null' : $id);
    }
}
