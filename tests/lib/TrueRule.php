<?php

namespace Test;

use JimTools\BasicAuth\Rules\RuleInterface;
use Psr\Http\Message\ServerRequestInterface;

class TrueRule implements RuleInterface
{
    public function __invoke(ServerRequestInterface $request): bool
    {
        return true;
    }
}
