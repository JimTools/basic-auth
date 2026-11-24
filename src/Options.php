<?php

declare(strict_types=1);

namespace JimTools\BasicAuth;

use JimTools\BasicAuth\Handlers\AfterHandlerInterface;
use JimTools\BasicAuth\Handlers\BeforeHandlerInterface;
use JimTools\BasicAuth\Rules\RuleInterface;

/**
 * @codeCoverageIgnore
 */
final class Options
{
    /**
     * @param list<string>         $relaxed
     * @param array<RuleInterface> $rules
     */
    public function __construct(
        public readonly bool $isSecure = true,
        public readonly array $relaxed = ['localhost', '127.0.0.1', '::1'],
        public readonly array $rules = [],
        public readonly string $realm = 'Protected',
        public ?BeforeHandlerInterface $before = null,
        public ?AfterHandlerInterface $after = null,
    ) {}
}
