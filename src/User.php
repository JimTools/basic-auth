<?php

declare(strict_types=1);

namespace JimTools\BasicAuth;

use SensitiveParameter;

/**
 * @codeCoverageIgnore
 */
final readonly class User
{
    public function __construct(
        #[SensitiveParameter]
        public string $username,
        #[SensitiveParameter]
        public string $password,
    ) {}
}
