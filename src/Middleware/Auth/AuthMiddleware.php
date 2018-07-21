<?php

namespace Fram\Middleware\Auth;

use Fram\Auth\Auth;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Generic middleware used to check if the user if connected and has the right
 * privileges.
 */
class AuthMiddleware implements MiddlewareInterface
{
    /**
     * @var Auth
     */
    private $auth;

    /**
     * @var array
     */
    private $roles;

    /**
     * Constructor.
     *
     * @param Auth $auth
     * @param array $roles The roles authorized.
     */
    public function __construct(Auth $auth, array $roles)
    {
        $this->auth = $auth;
        $this->roles = $roles;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $user = $this->auth->getUser();

        if ($user) {
            if ($this->hasRoles($user->getRoles())) {
                return $handler->handle($request);
            } else {
                throw new ForbiddenException();
            }
        } else {
            throw new NotConnectedException();
        }
    }

    /**
     * Checks if each role in $this->roles is effectively in $roles.
     *
     * @param array $roles
     * @return bool
     */
    private function hasRoles(array $roles): bool
    {
        foreach ($this->roles as $role) {
            if (in_array($role, $roles)) {
                return true;
            }
        }
    
        return false;
    }
}
