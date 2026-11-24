<?php

declare(strict_types=1);

namespace JimTools\BasicAuth\Handlers;

use Psr\Http\Message\ResponseInterface;

interface AfterHandlerInterface
{
    /**
     * @param array{decoded: array<string, mixed>, token: string} $arguments
     */
    public function __invoke(ResponseInterface $response, array $arguments): ResponseInterface;
}
