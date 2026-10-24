<?php

declare(strict_types=1);

namespace JimTools\BasicAuth;

use Psr\Http\Message\ServerRequestInterface;

/**
 * Rule to decide by request path whether the request should be authenticated or not.
 */
final class RequestPathRule implements RuleInterface
{
    /**
     * Stores all the options passed to the rule.
     *
     * @var mixed[]
     */
    private array $options = [
        'path' => ['/'],
        'ignore' => [],
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
        $uri = '/' . $request->getUri()->getPath();
        $uri = (string) preg_replace('#/+#', '/', $uri);

        // If request path is matches ignore should not authenticate.
        foreach ((array) $this->options['ignore'] as $ignore) {
            $ignore = rtrim($ignore, '/');
            if ((bool) preg_match("@^{$ignore}(/.*)?$@", $uri)) {
                return false;
            }
        }

        // Otherwise check if path matches and we should authenticate.
        foreach ((array) $this->options['path'] as $path) {
            $path = rtrim($path, '/');
            if ((bool) preg_match("@^{$path}(/.*)?$@", $uri)) {
                return true;
            }
        }

        return false;
    }
}
