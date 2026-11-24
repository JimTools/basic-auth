<?php

declare(strict_types=1);

namespace JimTools\BasicAuth\Tests;

use JimTools\BasicAuth\Rules\RequestPathRule;
use Laminas\Diactoros\ServerRequest;
use Laminas\Diactoros\Uri;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

/**
 * @internal
 */
#[CoversClass(RequestPathRule::class)]
final class RequestPathRuleTest extends TestCase
{
    public function testShouldAcceptArrayAndStringAsPath(): void
    {
        $request = (new ServerRequest())
            ->withUri(new Uri('https://example.com/admin/protected'))
            ->withMethod('GET');

        $rule = new RequestPathRule(['/admin']);
        self::assertTrue($rule($request));

        $rule = new RequestPathRule(['/admin']);
        self::assertTrue($rule($request));
    }

    public function testShouldAuthenticateEverything(): void
    {
        $request = (new ServerRequest())
            ->withUri(new Uri('https://example.com/'))
            ->withMethod('GET');

        $rule = new RequestPathRule(['/']);
        self::assertTrue($rule($request));

        $request = (new ServerRequest())
            ->withUri(new Uri('https://example.com/api'))
            ->withMethod('GET');

        self::assertTrue($rule($request));
    }

    public function testShouldAuthenticateOnlyApi(): void
    {
        $request = (new ServerRequest())
            ->withUri(new Uri('https://example.com/'))
            ->withMethod('GET');

        $rule = new RequestPathRule(['/api']);
        self::assertFalse($rule($request));

        $request = (new ServerRequest())
            ->withUri(new Uri('https://example.com/api'))
            ->withMethod('GET');

        self::assertTrue($rule($request));
    }

    public function testShouldAuthenticateCreateAndList(): void
    {
        // Authenticate only create and list actions
        $rule = new RequestPathRule(['/api/create', '/api/list']);

        // Should not authenticate
        $request = (new ServerRequest())
            ->withUri(new Uri('https://example.com/api'))
            ->withMethod('GET');
        self::assertFalse($rule($request));

        // Should authenticate
        $request = (new ServerRequest())
            ->withUri(new Uri('https://example.com/api/create'))
            ->withMethod('GET');
        self::assertTrue($rule($request));

        // Should authenticate
        $request = (new ServerRequest())
            ->withUri(new Uri('https://example.com/api/list'))
            ->withMethod('GET');
        self::assertTrue($rule($request));

        // Should not authenticate
        $request = (new ServerRequest())
            ->withUri(new Uri('https://example.com/api/ping'))
            ->withMethod('GET');
        self::assertFalse($rule($request));
    }

    public function testShouldIgnoreLogin(): void
    {
        $request = (new ServerRequest())
            ->withUri(new Uri('https://example.com/api'))
            ->withMethod('GET');

        $rule = new RequestPathRule(['/api'], ['/api/login']);
        self::assertTrue($rule($request));

        $request = (new ServerRequest())
            ->withUri(new Uri('https://example.com/api/login'))
            ->withMethod('GET');

        self::assertFalse($rule($request));
    }

    #[DataProvider('provideBug50ShouldAuthenticateMultipleSlashesCases')]
    public function testBug50ShouldAuthenticateMultipleSlashes(RequestInterface $request, bool $expected): void
    {
        $rule = new RequestPathRule(['/v1/api']);

        $request = (new ServerRequest())
            ->withUri(new Uri('https://example.com/'))
            ->withMethod('GET');
        self::assertFalse($rule($request));
        $request = (new ServerRequest())
            ->withUri(new Uri('https://example.com/v1/api'))
            ->withMethod('GET');
        self::assertTrue($rule($request));
        $request = (new ServerRequest())
            ->withUri(new Uri('https://example.com/v1//api'))
            ->withMethod('GET');
        self::assertTrue($rule($request));
        $request = (new ServerRequest())
            ->withUri(new Uri('https://example.com/v1//////api'))
            ->withMethod('GET');
        self::assertTrue($rule($request));
        $request = (new ServerRequest())
            ->withUri(new Uri('https://example.com//v1/api'))
            ->withMethod('GET');
        self::assertTrue($rule($request));
        $request = (new ServerRequest())
            ->withUri(new Uri('https://example.com//////v1/api'))
            ->withMethod('GET');
        self::assertTrue($rule($request));
    }

    /**
     * @return array<array-key, array{0:ServerRequest, 1: bool}>
     */
    public static function provideBug50ShouldAuthenticateMultipleSlashesCases(): iterable
    {
        return [
            [
                (new ServerRequest())
                    ->withUri(new Uri('https://example.com/'))
                    ->withMethod('GET'),
                false,
            ],
            [
                (new ServerRequest())
                    ->withUri(new Uri('https://example.com/v1/api'))
                    ->withMethod('GET'),
                true,
            ],
            [
                (new ServerRequest())
                    ->withUri(new Uri('https://example.com/v1//api'))
                    ->withMethod('GET'),
                true,
            ],
            [
                (new ServerRequest())
                    ->withUri(new Uri('https://example.com/v1//////api'))
                    ->withMethod('GET'),
                true,
            ],
            [
                (new ServerRequest())
                    ->withUri(new Uri('https://example.com//v1/api'))
                    ->withMethod('GET'),
                true,
            ],
            [
                (new ServerRequest())
                    ->withUri(new Uri('https://example.com/v1//////api'))
                    ->withMethod('GET'),
                true,
            ],
            [
                (new ServerRequest())
                    ->withUri(new Uri('https://example.com/v1//////api'))
                    ->withMethod('GET'),
                true,
            ],
            '/v1//////api' => [
                (new ServerRequest())
                    ->withUri(new Uri('https://example.com/v1//////api'))
                    ->withMethod('GET'),
                true,
            ],
            '//v1/api' => [
                (new ServerRequest())
                    ->withUri(new Uri('https://example.com//v1/api'))
                    ->withMethod('GET'),
                true,
            ],
            '//////v1/api' => [
                (new ServerRequest())
                    ->withUri(new Uri('https://example.com//////v1/api'))
                    ->withMethod('GET'),
                true,
            ],
        ];
    }
}
