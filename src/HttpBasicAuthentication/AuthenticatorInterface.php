<?php

declare(strict_types=1);

namespace JimTools\BasicAuth;

interface AuthenticatorInterface
{
    /**
     * @param string[] $arguments
     */
    public function __invoke(array $arguments): bool;
}
