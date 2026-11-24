<?php

declare(strict_types=1);

namespace JimTools\BasicAuth\Middleware;

use JimTools\BasicAuth\Authentication\AuthenticatorInterface;
use JimTools\BasicAuth\Options;
use JimTools\BasicAuth\Rules\RuleInterface;
use Laminas\Diactoros\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use RuntimeException;
use SensitiveParameter;
use SplStack;

final class HttpBasicAuthentication implements MiddlewareInterface
{
    /**
     * @var SplStack<RuleInterface>
     */
    private SplStack $rules;

    public function __construct(
        private readonly Options $options,
        private readonly AuthenticatorInterface $authenticator
    ) {
        // Setup stack for rules
        $this->rules = new SplStack();
        foreach ($options->rules as $rule) {
            $this->rules->push($rule);
        }
    }

    /**
     * Process a request in PSR-15 style and return a response.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $host = $request->getUri()->getHost();
        $scheme = $request->getUri()->getScheme();

        // If rules say we should not authenticate call next and return.
        if ($this->shouldAuthenticate($request) === false) {
            return $handler->handle($request);
        }

        // HTTP allowed only if secure is false or server is in relaxed array.
        if ($scheme !== 'https' && $this->options->isSecure) {
            $allowedHost = in_array($host, $this->options->relaxed, true);

            // if 'headers' is in the 'relaxed' key, then we check for forwarding
            $allowedForward = false;
            if (in_array('headers', $this->options->relaxed)) {
                if (
                    $request->getHeaderLine('X-Forwarded-Proto') === 'https'
                    && $request->getHeaderLine('X-Forwarded-Port') === '443'
                ) {
                    $allowedForward = true;
                }
            }

            if (!($allowedHost || $allowedForward)) {
                $message = sprintf(
                    'Insecure use of middleware over %s denied by configuration.',
                    strtoupper($scheme)
                );

                throw new RuntimeException($message);
            }
        }

        // Just in case.
        [$username, $password] = $this->getCredentialsFromHeader($request->getHeaderLine('Authorization'));

        // Check if user authenticates.
        if ($this->authenticator->__invoke($username, $password) === false) {
            // Set response headers before giving it to error callback
            return (new Response(status: 401))
                ->withHeader(
                    'WWW-Authenticate',
                    sprintf('Basic realm="%s"', $this->options->realm)
                );
        }

        // Modify $request before calling next middleware.
        if ($this->options->before !== null) {
            $request = call_user_func($this->options->before, $request, ['user' => $username, 'password' => $password]);
        }

        // Everything ok, call next middleware.
        $response = $handler->handle($request);

        // Modify $response before returning.
        if ($this->options->after !== null) {
            return call_user_func($this->options->after, $response, ['user' => $username, 'password' => $password]);
        }

        return $response;
    }

    /**
     * Set the rules which determine if current request should be authenticated.
     *
     * Rules must be callables which return a boolean. If any of the rules return
     * boolean false current request will not be authenticated.
     *
     * @param RuleInterface[] $rules
     */
    public function withRules(array $rules): self
    {
        $new = clone $this;
        // Clear the stack
        unset($new->rules);
        $new->rules = new SplStack();

        // Add the rules
        foreach ($rules as $rule) {
            $new = $new->addRule($rule);
        }

        return $new;
    }

    /**
     * Add a rule to the rules stack.
     *
     * Rules must be callables which return a boolean. If any of the rules return
     * boolean false current request will not be authenticated.
     */
    public function addRule(RuleInterface $rule): self
    {
        $new = clone $this;
        $new->rules = clone $this->rules;
        // @phpstan-ignore-next-line
        $new->rules->push($rule);

        return $new;
    }

    /**
     * @return array{0:null|string,1:null|string}
     */
    private function getCredentialsFromHeader(#[SensitiveParameter] string $header): array
    {
        if (preg_match('/Basic\s+(.*)$/i', $header, $matches)) {
            $explodedCredential = explode(':', base64_decode($matches[1]), 2);
            if (count($explodedCredential) == 2) {
                return $explodedCredential;
            }
        }

        return [null, null];
    }

    /**
     * Test if current request should be authenticated.
     */
    private function shouldAuthenticate(ServerRequestInterface $request): bool
    {
        // If any of the rules in stack return false will not authenticate
        foreach ($this->rules as $callable) {
            if (false === $callable($request)) {
                return false;
            }
        }

        return true;
    }
}
