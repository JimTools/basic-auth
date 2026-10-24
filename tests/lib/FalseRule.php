<?php

namespace Test;

use Psr\Http\Message\ServerRequestInterface;
use Tuupola\Middleware\HttpBasicAuthentication\RuleInterface;

class FalseRule implements RuleInterface
{
    public function __invoke(ServerRequestInterface $request): bool
    {
        return false;
    }
}
