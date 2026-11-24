<?php

namespace Test;

use Psr\Http\Message\ServerRequestInterface;
use JimTools\BasicAuth\Rules\RuleInterface;

class FalseRule implements RuleInterface
{
    public function __invoke(ServerRequestInterface $request): bool
    {
        return false;
    }
}
