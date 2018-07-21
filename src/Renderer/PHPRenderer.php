<?php

namespace Fram\Renderer;

use Fram\Renderer\RendererInterface;

/**
 * Renders the views.
 */
class PHPRenderer implements RendererInterface
{
    const DEFAULT_NAMESPACE = '__default';

    /**
     * @var string[]
     */
    private $paths = [];

    /**
     * @var array
     */
    private $globals = [];

    /**
     * @var array
     */
    private $layoutViews = [];

    /**
     * Constructor.
     *
     * @param string $defaultPath The path containing the default views.
     */
    public function __construct(string $defaultPath)
    {
        $this->paths[self::DEFAULT_NAMESPACE] = $defaultPath;
    }

    /**
     * Adds a path containing views to render.
     *
     * @param string $path
     * @param string $namespace Namespace associated with the path.
     */
    public function addPath(string $path, string $namespace): void
    {
        $this->paths[$namespace] = $path;
    }

    /**
     * Renders a view.
     *
     * We can specify to render in a specific namespace by adding '@' and the
     * namespace. The default extension is 'html.php'.
     *
     * If the view calls the 'extends' method, the extended view will be rendered
     * after the render of the view and the latter will be injected in the $content
     * variable of the layout view.
     *
     * @param string $view Path to the view (e.g. '@namespace/view').
     * @param array $params Key/value pairs containing the params to pass in the view.
     * @param string $extension File extension.
     * @return string The view rendered.
     */
    public function render(string $view, array $params = [], string $extension = 'html.php'): string
    {
        if ($view[0] === '@') {
            $namespace = substr($view, 1, strpos($view, '/') - 1);
            $path = str_replace('@' . $namespace, $this->paths[$namespace], $view);
        } else {
            $path = $this->paths[self::DEFAULT_NAMESPACE] . DIRECTORY_SEPARATOR . $view;
        }

        ob_start();
        extract($params);
        extract($this->globals);
        $renderer = $this;
        require $path . '.' . $extension;
        $render = ob_get_clean();

        if (isset($this->layoutViews[$view]) && $this->layoutViews[$view] !== null) {
            $layoutView = $this->layoutViews[$view][0];
            $layoutParams = $this->layoutViews[$view][1];
            $this->layoutViews[$view] = null;

            $params['content'] = $render;
            return $this->render($layoutView, array_merge($params, $layoutParams));
        }

        return $render;
    }

    /**
     * Adds a global variable accessible in all the views.
     *
     * @param string $key
     * @param mixed $value
     */
    public function addGlobal(string $key, $value): void
    {
        $this->globals[$key] = $value;
    }

    /**
     * Used to specify if a view extends another.
     *
     * To extend a view, this method must be called at the beginning of the 'child'
     * view like this : $renderer->extends('your_layout_view', $view).
     * In any view, $view is the current view.
     *
     * A 'layout view' is a view intended to be extended and containing a $content
     * variable which contains the extension view.
     *
     * @param string $layoutView
     * @param string $currentView
     * @param array $params Parameters to inject in the layout view.
     */
    public function extends(string $layoutView, string $currentView, array $params = []): void
    {
        if (!array_key_exists($currentView, $this->layoutViews)) {
            $this->layoutViews[$currentView] = [$layoutView, $params];
        }
    }
}
