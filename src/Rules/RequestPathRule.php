<?php

declare(strict_types=1);

namespace JimTools\BasicAuth\Rules;

use Psr\Http\Message\ServerRequestInterface;

/**
 * Rule to decide by request path whether the request should be authenticated or not.
 */
final readonly class RequestPathRule implements RuleInterface
{
    /**
     * @param list<string> $path
     * @param list<string> $ignore
     */
    public function __construct(
        private array $path = ['/'],
        private array $ignore = []
    ) {}

    public function __invoke(ServerRequestInterface $request): bool
    {
        $uri = '/' . $request->getUri()->getPath();
        $uri = (string) preg_replace('#/+#', '/', $uri);

        // If request path is matches ignore should not authenticate.
        foreach ($this->ignore as $ignore) {
            $ignore = rtrim($ignore, '/');
            if (preg_match("@^{$ignore}(/.*)?$@", $uri)) {
                return false;
            }
        }

        // Otherwise check if path matches and we should authenticate.
        foreach ($this->path as $path) {
            $path = rtrim($path, '/');
            if (preg_match("@^{$path}(/.*)?$@", $uri)) {
                return true;
            }
        }

        return false;
    }
}
