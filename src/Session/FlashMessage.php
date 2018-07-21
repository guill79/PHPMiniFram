<?php

namespace Fram\Session;

use Fram\Session\SessionInterface;

/**
 * Represents a message visible once.
 */
class FlashMessage
{
    const SESSION_KEY = 'flash';

    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * @var array
     */
    private $messages;

    /**
     * Constructor.
     *
     * @param SessionInterface $session
     */
    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    /**
     * Adds a message.
     *
     * @param string $type The type of the message.
     * @param string $message The message.
     */
    public function add(string $type, string $message): void
    {
        $flash = $this->session->get(self::SESSION_KEY, []);
        $flash[$type] = $message;
        $this->session->set(self::SESSION_KEY, $flash);
    }

    /**
     * Returns the message corresponding to the type.
     *
     * @param string $type
     * @return string|null
     */
    public function get(string $type): ?string
    {
        if ($this->messages === null) {
            $this->messages = $this->session->get(self::SESSION_KEY, []);
            $this->session->delete(self::SESSION_KEY);
        }
        return $this->messages[$type] ?? null;
    }

    /**
     * Deletes the messages from the session.
     */
    public function clear()
    {
        $this->session->delete(self::SESSION_KEY);
    }
}
