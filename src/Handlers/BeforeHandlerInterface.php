<?php

declare(strict_types=1);

namespace JimTools\BasicAuth\Handlers;

use Psr\Http\Message\ServerRequestInterface;

interface BeforeHandlerInterface
{
    public function __invoke(ServerRequestInterface $request, array $arguments): ServerRequestInterface;
}
