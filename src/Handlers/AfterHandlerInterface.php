<?php

declare(strict_types=1);

namespace JimTools\BasicAuth\Handlers;

use Psr\Http\Message\ResponseInterface;

interface AfterHandlerInterface
{
    public function __invoke(ResponseInterface $response, ?string $username, ?string $password): ResponseInterface;
}
