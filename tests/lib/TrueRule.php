<?php

namespace Test;

use Psr\Http\Message\ServerRequestInterface;
use JimTools\BasicAuth\Rules\RuleInterface;

class TrueRule implements RuleInterface
{
    public function __invoke(ServerRequestInterface $request): bool
    {
        return true;
    }
}
