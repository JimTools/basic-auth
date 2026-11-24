<?php

declare(strict_types=1);

namespace Test;

use JimTools\BasicAuth\Rules\RuleInterface;
use Psr\Http\Message\ServerRequestInterface;

class FalseRule implements RuleInterface
{
    public function __invoke(ServerRequestInterface $request): bool
    {
        return false;
    }
}
