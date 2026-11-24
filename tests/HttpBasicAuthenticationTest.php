<?php

namespace JimTools\BasicAuth\Tests;

use JimTools\BasicAuth\Options;
use JimTools\BasicAuth\Rules\RuleInterface;
use Equip\Dispatch\MiddlewareCollection;
use JimTools\BasicAuth\Authentication\ArrayAuthenticator;
use JimTools\BasicAuth\Handlers\AfterHandlerInterface;
use JimTools\BasicAuth\Handlers\BeforeHandlerInterface;
use JimTools\BasicAuth\Middleware\HttpBasicAuthentication;
use JimTools\BasicAuth\Rules\RequestMethodRule;
use JimTools\BasicAuth\Rules\RequestPathRule;
use JimTools\BasicAuth\User;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Test\FalseAuthenticator;
use Test\TrueAuthenticator;
use Test\TrueRule;
use Tuupola\Http\Factory\ResponseFactory;
use Tuupola\Http\Factory\ServerRequestFactory;
use Tuupola\Http\Factory\StreamFactory;

/**
 * @internal
 */
#[CoversClass(HttpBasicAuthentication::class)]
class HttpBasicAuthenticationTest extends TestCase
{
    protected HttpBasicAuthentication $middleware;
    protected ArrayAuthenticator $authenticator;

    protected function setUp(): void
    {
        $this->authenticator = new ArrayAuthenticator([
            new User('root', 't00r'),
            new User('user', 'passw0rd'),
        ]);
        $this->middleware = new HttpBasicAuthentication(
            new Options(rules: [new RequestMethodRule(), new RequestPathRule(['/admin'])]),
            $this->authenticator,
        );
    }

    private static function nextHandler(): RequestHandlerInterface
    {
        return new class () implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                $response = (new ResponseFactory())->createResponse();
                $response->getBody()->write('Success');

                return $response;
            }
        };
    }

    public function testShouldReturn200WithoutPassword()
    {
        $request = (new ServerRequestFactory())
            ->createServerRequest('GET', 'https://example.com/public');

        $response = $this->middleware->process($request, $this->nextHandler());

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('Success', (string) $response->getBody());
    }

    public function testShouldReturn401WithoutPassword()
    {
        $request = (new ServerRequestFactory())
            ->createServerRequest('GET', 'https://example.com/admin/item');

        $middleware = new HttpBasicAuthentication(
            new Options(rules: [new RequestPathRule(['/admin'])], realm: 'Not sure'),
            $this->authenticator,
        );

        $response = $middleware->process($request, self::nextHandler());;

        self::assertSame(401, $response->getStatusCode());
        self::assertSame('Basic realm="Not sure"', $response->getHeaderline('WWW-Authenticate'));
        self::assertSame('', (string) $response->getBody());
    }

    public function testShouldReturn200WithPassword()
    {
        $request = (new ServerRequestFactory())
            ->createServerRequest('GET', 'https://example.com/admin/item')
            ->withHeader('Authorization', 'Basic cm9vdDp0MDBy');

        $response = $this->middleware->process($request, self::nextHandler());

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('Success', (string)$response->getBody());
    }

    public function testShouldReturn200WithOptions()
    {
        $request = (new ServerRequestFactory())
            ->createServerRequest('OPTIONS', 'https://example.com/admin/item');

        $response = $this->middleware->process($request, self::nextHandler());

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('Success', (string)$response->getBody());
    }

    public function testShouldReturn200WithoutPasswordWithAnonymousFunction()
    {
        $request = (new ServerRequestFactory())
            ->createServerRequest('GET', 'https://example.com/admin/item');


        $auth = $this->middleware->addrule(new class () implements RuleInterface {
            public function __invoke(ServerRequestInterface $request): bool
            {
                return false;
            }
        });

        $response = $auth->process($request, self::nextHandler());

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('Success', (string)$response->getBody());
    }

    public function testShouldReturn200WithIgnore()
    {
        $request = (new ServerRequestFactory())
            ->createServerRequest('GET', 'https://example.com/admin/ping');

        $auth = new HttpBasicAuthentication(
            new Options(rules: [new RequestPathRule(['/admin'], ['/admin/ping'])]),
            $this->authenticator,
        );
        $response = $auth->process($request, self::nextHandler());

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('Success', (string) $response->getBody());
    }

    public function testShouldReturn401WithFromAfter()
    {
        $request = (new ServerRequestFactory())
            ->createServerRequest('GET', 'https://example.com/admin/item')
            ->withHeader('Authorization', 'Basic cm9vdDp0MDBy');

        $options = new Options(
            rules: [new RequestPathRule(['/admin'])],
            after: new class () implements AfterHandlerInterface {
                public function __invoke(ResponseInterface $response, array $arguments): ResponseInterface
                {
                    return $response
                        ->withBody((new StreamFactory())->createStream())
                        ->withStatus(401)
                        ->withHeader('WWW-Authenticate', 'Basic realm="Go away!"');
                }
            }
        );

        $auth = new HttpBasicAuthentication($options, $this->authenticator);

        $response = $auth->process($request, self::nextHandler());

        self::assertSame(401, $response->getStatusCode());
        self::assertSame('Basic realm="Go away!"', $response->getHeaderline('WWW-Authenticate'));
        self::assertSame('', (string)$response->getBody());
    }

    public function testShouldAlterResponseWithAfter()
    {
        $request = (new ServerRequestFactory())
            ->createServerRequest('GET', 'https://example.com/admin/item')
            ->withHeader('Authorization', 'Basic cm9vdDp0MDBy');

        $auth = new HttpBasicAuthentication(
            new Options(
                rules: [new RequestPathRule(['/admin'])],
                after: new class () implements AfterHandlerInterface {
                    public function __invoke(ResponseInterface $response, array $arguments): ResponseInterface
                    {
                        return $response->withHeader('X-Brawndo', 'plants crave');
                    }
                }
            ),
            $this->authenticator,
        );

        $response = $auth->process($request, self::nextHandler());

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('plants crave', $response->getHeaderLine('X-Brawndo'));
    }

    public function testShouldReturn200WithTrueAuthenticator()
    {
        $request = (new ServerRequestFactory())
            ->createServerRequest('GET', 'https://example.com/admin/item');

        $response = new HttpBasicAuthentication(new Options(), new TrueAuthenticator)
            ->process($request, self::nextHandler());

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('Success', (string) $response->getBody());
    }

    public function testShouldReturn401WithFalseAuthenticator()
    {
        $request = (new ServerRequestFactory())
            ->createServerRequest('GET', 'https://example.com/admin/item');

        $auth = new HttpBasicAuthentication(new Options(), new FalseAuthenticator);

        $response = $auth->process($request, self::nextHandler());

        self::assertSame(401, $response->getStatusCode());
        self::assertSame('', (string) $response->getBody());
    }

    public function testShouldModifyRequestUsingBefore()
    {
        $request = (new ServerRequestFactory())
            ->createServerRequest('GET', 'https://example.com/admin/item')
            ->withHeader('Authorization', 'Basic cm9vdDp0MDBy');

        $auth = new HttpBasicAuthentication(
            new Options(
                rules: [new RequestPathRule(['/admin'])],
                before: new class () implements BeforeHandlerInterface {
                    public function __invoke(ServerRequestInterface $request, array $arguments): ServerRequestInterface
                    {
                        return $request->withAttribute('user', $arguments['user']);
                    }
                }
            ),
            $this->authenticator
        );

        $handler = new class () implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                $response = (new ResponseFactory())->createResponse();
                $response->getBody()->write($request->getAttribute('user'));

                return $response;
            }
        };

        $response = $auth->process($request, $handler);

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('root', (string)$response->getBody());
    }

    public function testShouldNotAllowInsecure()
    {
        $this->expectException('RuntimeException');

        $request = (new ServerRequestFactory())
            ->createServerRequest('GET', 'http://example.com/api');

        $auth = new HttpBasicAuthentication(
            new Options(rules: [new RequestPathRule(['/api'])]),
            $this->authenticator,
        );

        $auth->process($request, self::nextHandler());
    }

    public function testShouldRelaxInsecureInLocalhost()
    {
        $request = (new ServerRequestFactory())
            ->createServerRequest('GET', 'http://localhost/api');

        $response = (new ResponseFactory())->createResponse();

        $auth = new HttpBasicAuthentication(
            new Options(
                rules: [new RequestPathRule(['/api'])],
            ),
            $this->authenticator
        );

        $response = $auth->process($request, self::nextHandler());

        self::assertSame(401, $response->getStatusCode());
    }

    public function testShouldRelaxInsecureViaSetting()
    {
        $request = (new ServerRequestFactory())
            ->createServerRequest('GET', 'http://example.com/api');

        $auth = new HttpBasicAuthentication(
            new Options(
                rules: [new RequestPathRule(['/api'])],
                relaxed: ['localhost', 'example.com'],
            ),
            $this->authenticator
        );

        $response = $auth->process($request, self::nextHandler());

        self::assertSame(401, $response->getStatusCode());
    }

    public function testShouldRelaxForwardedViaSetting()
    {
        $request = (new ServerRequestFactory())
            ->createServerRequest('GET', 'http://example.com/api')
            ->withHeader('X-Forwarded-Proto', 'https')
            ->withHeader('X-Forwarded-Port', '443');

        $auth = new HttpBasicAuthentication(
            new Options(
                relaxed: ['localhost', 'headers'],
                rules: [new RequestPathRule(['/api'])],
            ),
            $this->authenticator,
        );

        $response = $auth->process($request, self::nextHandler());

        self::assertSame(401, $response->getStatusCode());
    }

    public function testShouldBeImmutable()
    {
        $auth1 = new HttpBasicAuthentication(
            new Options(rules: [new RequestMethodRule(), new RequestPathRule(['/api'])]),
            $this->authenticator,
        );
        $auth2 = $auth1->addRule(new TrueRule);
        $auth3 = $auth1->withRules([new TrueRule]);

        // Closure kludge to test private properties.
        $self = $this;

        $closure = function () use ($self) {
            $self::assertSame(2, count($this->rules));
        };
        call_user_func($closure->bindTo($auth1, HttpBasicAuthentication::class));

        $closure = function () use ($self) {
            $self::assertSame(3, count($this->rules));
        };
        call_user_func($closure->bindTo($auth2, HttpBasicAuthentication::class));

        $closure = function () use ($self) {
            $self::assertSame(1, count($this->rules));
        };
        call_user_func($closure->bindTo($auth3, HttpBasicAuthentication::class));
    }

    public function testShouldHandlePsr15()
    {
        $request = (new ServerRequestFactory())
            ->createServerRequest('GET', 'https://example.com/');

        $default = function (RequestInterface $request) {
            $response = (new ResponseFactory())->createResponse();
            $response->getBody()->write('Success');

            return $response;
        };
        $collection = new MiddlewareCollection([
            new HttpBasicAuthentication(new Options(), $this->authenticator),
        ]);
        $response = $collection->dispatch($request, $default);

        self::assertSame(401, $response->getStatusCode());
        self::assertSame('', (string) $response->getBody());
    }

    public function testShouldHandleRulesArrayBug()
    {
        $request = (new ServerRequestFactory())
            ->createServerRequest('GET', 'https://example.com/api');

        $default = function (ServerRequestInterface $request) {
            $response = (new ResponseFactory())->createResponse();
            $response->getBody()->write('Success');

            return $response;
        };

        $collection = new MiddlewareCollection([
            new HttpBasicAuthentication(
                new Options(
                    rules: [
                        new RequestPathRule(['/api'], ['/api/login']),
                        new RequestMethodRule()
                    ]
                ),
                $this->authenticator,
            ),
        ]);

        $response = $collection->dispatch($request, $default);

        self::assertSame(401, $response->getStatusCode());
        self::assertSame('', (string) $response->getBody());

        $request = (new ServerRequestFactory())
            ->createServerRequest('GET', 'https://example.com/api/login');

        $response = $collection->dispatch($request, $default);

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('Success', (string) $response->getBody());
    }

    // BUGS

    public function testBug2UrlShouldMatchRegex()
    {
        $request = (new ServerRequestFactory())
            ->createServerRequest('GET', 'https://example.com/status/foo');

        $auth = new HttpBasicAuthentication(
            new Options(rules: [new RequestPathRule(['/stat'])]),
            $this->authenticator
        );

        $response = $auth->process($request, self::nextHandler());

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('Success', (string)$response->getBody());
    }

    public function testBug3ShouldReturn401WithoutTrailingSlash()
    {
        $request = (new ServerRequestFactory())
            ->createServerRequest('GET', 'https://example.com/admin');

        $response = $this->middleware->process($request, self::nextHandler());

        self::assertSame(401, $response->getStatusCode());
        self::assertSame('', (string) $response->getBody());
    }

    public function testBug3ShouldReturn401WithTrailingSlash()
    {
        $request = (new ServerRequestFactory())
            ->createServerRequest('GET', 'https://example.com/admin');

        $response = $this->middleware->process($request, self::nextHandler());

        self::assertSame(401, $response->getStatusCode());
        self::assertSame('', (string) $response->getBody());
    }

    public function testBug9ShouldAllowUnauthenticatedHttp()
    {
        $request = (new ServerRequestFactory())
            ->createServerRequest('GET', 'https://example.com/public/foo');

        $auth = new HttpBasicAuthentication(
            new Options(rules: [new RequestPathRule(['/api', '/bar'])]),
            $this->authenticator,
        );

        $response = $auth->process($request, self::nextHandler());

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('Success', (string) $response->getBody());
    }

    public function testBug31ShouldAllowColonInPassword()
    {
        $request = (new ServerRequestFactory())
            ->createServerRequest('GET', 'https://example.com/api/foo')
            ->withHeader('Authorization', 'Basic Zm9vOmJhcjpwb3A=');

        $auth = new HttpBasicAuthentication(
            new Options(rules: [new RequestPathRule(['/api', '/bar'])]),
            new ArrayAuthenticator([new User('foo', 'bar:pop')]),
        );

        $response = $auth->process($request, self::nextHandler());

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('Success', (string)$response->getBody());
    }

    public function testPull59ShouldNotErrorWithMalformedCredentials()
    {
        $request = (new ServerRequestFactory())
            ->createServerRequest('GET', 'https://example.com/api/foo')
            ->withHeader('Authorization', 'Basic Zm9vCg=='); // foo

        $auth = new HttpBasicAuthentication(
            new Options(rules: [new RequestPathRule(['/api', '/bar'])]),
            new ArrayAuthenticator([new User('foo', 'bar')])
        );
        $response = $auth->process($request, self::nextHandler());

        self::assertSame(401, $response->getStatusCode());
        self::assertSame('', (string) $response->getBody());
    }
}
