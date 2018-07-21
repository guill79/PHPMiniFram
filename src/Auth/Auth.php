<?php

namespace Fram\Auth;

/**
 * Interface to implement in authentification modules.
 */
interface Auth
{
    /**
     * Retrieves the current logged user.
     * @return User|null
     */
    public function getUser();
}
