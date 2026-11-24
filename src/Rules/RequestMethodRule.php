<?php

declare(strict_types=1);

namespace JimTools\BasicAuth\Rules;

use Psr\Http\Message\ServerRequestInterface;

final readonly class RequestMethodRule implements RuleInterface
{
    /**
     * @param list<string> $ignore
     */
    public function __construct(private array $ignore = ['OPTIONS'])
    {
    }

    public function __invoke(ServerRequestInterface $request): bool
    {
        return !in_array($request->getMethod(), $this->ignore, true);
    }
}
