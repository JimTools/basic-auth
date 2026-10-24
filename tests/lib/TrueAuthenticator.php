<?php

namespace Test;

use Tuupola\Middleware\HttpBasicAuthentication\AuthenticatorInterface;

class TrueAuthenticator implements AuthenticatorInterface
{
    public function __invoke(array $arguments): bool
    {
        return true;
    }
}
