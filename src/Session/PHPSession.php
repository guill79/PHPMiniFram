<?php

namespace Fram\Session;

use Fram\Session\SessionInterface;

/**
 * Implements a PHP session.
 */
class PHPSession implements SessionInterface
{
    /**
     * Ensure that the session is started.
     */
    private function ensureStarted(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Checks if a value corresponding to the key is stored in session.
     *
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool
    {
        $this->ensureStarted();
        return array_key_exists($key, $_SESSION);
    }

    /**
     * Retrieves an information in session.
     *
     * @param string $key
     * @param mixed $default The default value to return.
     * @return mixed
     */
    public function get(string $key, $default = '')
    {
        $this->ensureStarted();
        if ($this->has($key)) {
            return $_SESSION[$key];
        }
        return $default;
    }

    /**
     * Adds an information in session.
     *
     * @param string $key
     * @param type $value
     */
    public function set(string $key, $value): void
    {
        $this->ensureStarted();
        $_SESSION[$key] = $value;
    }

    /**
     * Deletes an information from the session.
     * @param string $key
     */
    public function delete(string $key): void
    {
        $this->ensureStarted();
        unset($_SESSION[$key]);
    }
}
