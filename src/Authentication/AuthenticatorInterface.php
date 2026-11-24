<?php

declare(strict_types=1);

namespace JimTools\BasicAuth\Authentication;

use SensitiveParameter;

interface AuthenticatorInterface
{
    public function __invoke(
        #[SensitiveParameter] ?string $username,
        #[SensitiveParameter] ?string $password
    ): bool;
}
