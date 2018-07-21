<?php

namespace Tests\PHPRenderer;

use Fram\Renderer\PHPRenderer;
use PHPUnit\Framework\TestCase;

class PHPRendererTest extends TestCase
{
    private $renderer;

    public function setUp()
    {
        $this->renderer = new PHPRenderer(__DIR__ . '/view');
    }

    public function testRenderDefaultPath()
    {
        $page = $this->renderer->render('index');
        $this->assertEquals("Je suis l'index du dossier view.", $page);
    }

    public function testRenderNamespacedPath()
    {
        $this->renderer->addPath(__DIR__ . '/view/demo', 'demo');
        $page = $this->renderer->render('@demo/index');
        $this->assertEquals('Page demo', $page);

        $page = $this->renderer->render('demo/index');
        $this->assertEquals('Page demo', $page);
    }

    public function testRenderSubFolder()
    {
        $this->renderer->addPath(__DIR__ . '/view/demo', 'demo');
        $page = $this->renderer->render('@demo/sub/sub');
        $this->assertEquals('yo', $page);
    }

    public function testRenderWithParams()
    {
        $page = $this->renderer->render('demo/params', [
            'param' => 'salut'
        ]);
        $this->assertEquals("J'attends un paramètre : salut", $page);
    }

    public function testRenderGlobalParam()
    {
        $this->renderer->addGlobal('param', 'toto');
        $page = $this->renderer->render('demo/params');
        $this->assertEquals("J'attends un paramètre : toto", $page);
    }

    public function testRenderOtherExtension()
    {
        $page = $this->renderer->render('test', [], 'txt');
        $this->assertEquals('Fichier texte', $page);

        $page = $this->renderer->render('demo/toto', [], 'html');
        $this->assertEquals('<h1>Toto !</h1>', $page);
    }

    public function testRenderWithLayoutView()
    {
        $this->renderer->addPath(__DIR__ . '/view/toto', 'toto');
        $render = $this->renderer->render('@toto/sub');

        $this->assertEquals('le contenu : je suis le contenu. Fin du contenu.', $render);
    }

    public function testRenderWithLayoutViewAndParams()
    {
        $this->renderer->addPath(__DIR__ . '/view/toto', 'toto');
        $render = $this->renderer->render('@toto/subParams');

        $this->assertEquals('salut.le contenu : ceci est le contenu. Fin du contenu.', $render);
    }

    public function testRenderWithLayoutViewAndParamsNested()
    {
        $this->renderer->addPath(__DIR__ . '/view/toto', 'toto');
        $render = $this->renderer->render('@toto/subSubParams');

        $this->assertEquals('salut.le contenu : ceci est le contenusubsub. Fin du contenu.', $render);
    }
}
