<?php

namespace Test;

use Tuupola\Middleware\HttpBasicAuthentication\AuthenticatorInterface;

class FalseAuthenticator implements AuthenticatorInterface
{
    public function __invoke(array $arguments): bool
    {
        return false;
    }
}
