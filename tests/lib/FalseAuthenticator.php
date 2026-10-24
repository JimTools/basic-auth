<?php

namespace Test;

use JimTools\BasicAuth\AuthenticatorInterface;

class FalseAuthenticator implements AuthenticatorInterface
{
    public function __invoke(array $arguments): bool
    {
        return false;
    }
}
