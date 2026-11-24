<?php

namespace Test;

use JimTools\BasicAuth\Authentication\AuthenticatorInterface;

class TrueAuthenticator implements AuthenticatorInterface
{
    public function __invoke(?string $username, ?string $password): bool
    {
        return true;
    }
}
