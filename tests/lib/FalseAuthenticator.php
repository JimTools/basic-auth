<?php

namespace Test;


use JimTools\BasicAuth\Authentication\AuthenticatorInterface;

class FalseAuthenticator implements AuthenticatorInterface
{
    public function __invoke(?string $username, ?string $password): bool
    {
        return false;
    }
}
