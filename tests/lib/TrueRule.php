<?php

namespace Test;

use Psr\Http\Message\ServerRequestInterface;
use Tuupola\Middleware\HttpBasicAuthentication\RuleInterface;

class TrueRule implements RuleInterface
{
    public function __invoke(ServerRequestInterface $request): bool
    {
        return true;
    }
}
