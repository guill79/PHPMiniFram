<?php

namespace Tests\Session;

use Fram\Session\SessionInterface;

class ArraySession implements SessionInterface
{
    private $session = [];

    /**
     * Checks whether the Session has the key.
     * 
     * @param string $key The key to check.
     * @return bool true if it has, false else.
     */
    public function has(string $key): bool
    {
        return array_key_exists($key, $this->session);
    }

    /**
     * Retrieve the entry of the Session according to the key.
     *
     * @param string $key The key we want to find the value of.
     * @param mixed $default The default return value if the value does not exists.
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        if ($this->has($key)) {
            return $this->session[$key];
        }
        return $default;
    }

    /**
     * Adds an entry to the Session.
     *
     * @param string $key
     * @param mixed $value
     */
    public function set(string $key, $value): void
    {
        $this->session[$key] = $value;
    }

    /**
     * Deletes an entry from the Session.
     *
     * @param string $key
     */
    public function delete(string $key): void
    {
        unset($this->session[$key]);
    }
}
