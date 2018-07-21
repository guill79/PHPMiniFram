<?php

namespace Fram\Renderer;

/**
 * Interface defining a renderer.
 * Not very useful as only PHPRenderer is used.
 */
interface RendererInterface
{
    public function addPath(string $path, string $shortcut): void;

    public function render(string $view, array $params = [], string $extension = '.html.php'): string;

    public function addGlobal(string $key, $value): void;
}
