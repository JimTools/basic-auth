<?php

declare(strict_types=1);

namespace Tuupola\Middleware\HttpBasicAuthentication;

use Psr\Http\Message\ServerRequestInterface;

final class RequestMethodRule implements RuleInterface
{
    /**
     * Stores all the options passed to the rule.
     *
     * @var mixed[]
     */
    private $options = [
        'ignore' => ['OPTIONS'],
    ];

    /**
     * @param mixed[] $options
     */
    public function __construct(array $options = [])
    {
        $this->options = array_merge($this->options, $options);
    }

    public function __invoke(ServerRequestInterface $request): bool
    {
        return !in_array($request->getMethod(), $this->options['ignore']);
    }
}
