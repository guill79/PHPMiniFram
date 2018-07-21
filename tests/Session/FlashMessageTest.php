<?php

namespace Tests\Session;

use Fram\Session\FlashMessage;
use PHPUnit\Framework\TestCase;
use Tests\Session\ArraySession;

class FlashMessageTest extends TestCase
{
    private $flash;
    private $session;

    public function setUp()
    {
        $this->session = new ArraySession();
        $this->flash = new FlashMessage($this->session);
    }

    public function testAddMessageInSession()
    {
        $this->flash->add('success', 'Opération réussie');
        $this->assertEquals(
            ['success' => 'Opération réussie'],
            $this->session->get(FlashMessage::SESSION_KEY)
        );
    }

    public function testAddManyMessagesInSession()
    {
        $this->flash->add('success', 'Opération réussie');
        $this->flash->add('error', 'Failure');
        $this->assertEquals(
            [
                'success' => 'Opération réussie',
                'error' => 'Failure'
            ],
            $this->session->get(FlashMessage::SESSION_KEY)
        );

        $this->flash->add('toto', 'salut');
        $this->assertEquals(
            [
                'success' => 'Opération réussie',
                'error' => 'Failure',
                'toto' => 'salut'
            ],
            $this->session->get(FlashMessage::SESSION_KEY)
        );
    }

    public function testGetMessage()
    {
        $this->flash->add('error', 'An error has occured');
        $this->assertEquals('An error has occured', $this->flash->get('error'));
        $this->assertNull($this->session->get(FlashMessage::SESSION_KEY));
        // Testing multiple get to test persistence
        $this->assertEquals('An error has occured', $this->flash->get('error'));
        $this->assertEquals('An error has occured', $this->flash->get('error'));
    }
}
