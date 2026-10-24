<?php

namespace Test;

use JimTools\BasicAuth\AuthenticatorInterface;

class TrueAuthenticator implements AuthenticatorInterface
{
    public function __invoke(array $arguments): bool
    {
        return true;
    }
}
