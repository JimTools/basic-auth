<?php

namespace Test;

use JimTools\BasicAuth\Authentication\AuthenticatorInterface;
use SensitiveParameter;

class TrueAuthenticator implements AuthenticatorInterface
{
    public function __invoke(#[SensitiveParameter] ?string $username, #[SensitiveParameter] ?string $password): bool
    {
        return true;
    }
}
