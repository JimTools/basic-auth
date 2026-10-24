<?php

declare(strict_types=1);

namespace Tuupola\Middleware\HttpBasicAuthentication;

interface AuthenticatorInterface
{
    /**
     * @param string[] $arguments
     */
    public function __invoke(array $arguments): bool;
}
