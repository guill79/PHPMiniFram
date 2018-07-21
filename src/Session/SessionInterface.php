<?php

namespace Fram\Session;

/**
 * Interface defining a session.
 */
interface SessionInterface
{
    /**
     * Checks whether the session has the key.
     *
     * @param string $key The key to check.
     * @return bool true if it has, false else.
     */
    public function has(string $key): bool;

    /**
     * Retrieve the entry of the session according to the key.
     *
     * @param string $key The key we want to find the value of.
     * @param mixed $default The default return value if the value does not exists.
     * @return mixed
     */
    public function get(string $key, $default = null);

    /**
     * Adds an entry to the session.
     *
     * @param string $key
     * @param mixed $value
     */
    public function set(string $key, $value): void;

    /**
     * Deletes an entry from the session.
     *
     * @param string $key
     */
    public function delete(string $key): void;
}
