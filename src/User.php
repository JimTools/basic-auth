<?php

namespace JimTools\BasicAuth;

/**
 * @codeCoverageIgnore
 */
final readonly class User
{
    public function __construct(
        #[\SensitiveParameter]
        public string $username,
        #[\SensitiveParameter]
        public string $password,
    )
    {
    }
}
