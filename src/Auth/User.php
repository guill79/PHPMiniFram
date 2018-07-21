<?php

namespace Fram\Auth;

/**
 * Interface to implement in authentification modules.
 */
interface User
{
    /**
     * Returns the user's ID.
     * @return int
     */
    public function getId(): int;

    /**
     * Returns the user's roles.
     * @return array
     */
    public function getRoles(): array;
}
