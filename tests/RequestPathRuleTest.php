<?php

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
class RequestPathRuleTest extends TestCase
{
    public function testShouldAcceptArrayAndStringAsPath()
    {
        $request = (new ServerRequest())
            ->withUri(new Uri('https://example.com/admin/protected'))
            ->withMethod('GET');

        $rule = new RequestPathRule(['/admin']);
        $this->assertTrue($rule($request));

        $rule = new RequestPathRule(['/admin']);
        $this->assertTrue($rule($request));
    }

    public function testShouldAuthenticateEverything()
    {
        $request = (new ServerRequest())
            ->withUri(new Uri('https://example.com/'))
            ->withMethod('GET');

        $rule = new RequestPathRule(['/']);
        $this->assertTrue($rule($request));

        $request = (new ServerRequest())
            ->withUri(new Uri('https://example.com/api'))
            ->withMethod('GET');

        $this->assertTrue($rule($request));
    }

    public function testShouldAuthenticateOnlyApi()
    {
        $request = (new ServerRequest())
            ->withUri(new Uri('https://example.com/'))
            ->withMethod('GET');

        $rule = new RequestPathRule(['/api']);
        $this->assertFalse($rule($request));

        $request = (new ServerRequest())
            ->withUri(new Uri('https://example.com/api'))
            ->withMethod('GET');

        $this->assertTrue($rule($request));
    }

    public function testShouldAuthenticateCreateAndList()
    {
        // Authenticate only create and list actions
        $rule = new RequestPathRule(['/api/create', '/api/list']);

        // Should not authenticate
        $request = (new ServerRequest())
            ->withUri(new Uri('https://example.com/api'))
            ->withMethod('GET');
        $this->assertFalse($rule($request));

        // Should authenticate
        $request = (new ServerRequest())
            ->withUri(new Uri('https://example.com/api/create'))
            ->withMethod('GET');
        $this->assertTrue($rule($request));

        // Should authenticate
        $request = (new ServerRequest())
            ->withUri(new Uri('https://example.com/api/list'))
            ->withMethod('GET');
        $this->assertTrue($rule($request));

        // Should not authenticate
        $request = (new ServerRequest())
            ->withUri(new Uri('https://example.com/api/ping'))
            ->withMethod('GET');
        $this->assertFalse($rule($request));
    }

    public function testShouldIgnoreLogin()
    {
        $request = (new ServerRequest())
            ->withUri(new Uri('https://example.com/api'))
            ->withMethod('GET');

        $rule = new RequestPathRule(['/api'], ['/api/login']);
        $this->assertTrue($rule($request));

        $request = (new ServerRequest())
            ->withUri(new Uri('https://example.com/api/login'))
            ->withMethod('GET');

        $this->assertFalse($rule($request));
    }

    public static function multipleSlashesProvider(): array
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
                true
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
            '//v1/api' =>  [
                (new ServerRequest())
                    ->withUri(new Uri('https://example.com//v1/api'))
                    ->withMethod('GET'),
                true,
            ],
            '//////v1/api'  => [
                (new ServerRequest())
                    ->withUri(new Uri('https://example.com//////v1/api'))
                    ->withMethod('GET'),
                true,
            ]
        ];
    }

    #[DataProvider('multipleSlashesProvider')]
    public function testBug50ShouldAuthenticateMultipleSlashes(RequestInterface $request, bool $expected)
    {

        $rule = new RequestPathRule([
            'path' => '/v1/api',
        ]);

        $request = (new ServerRequest())
            ->withUri(new Uri('https://example.com/'))
            ->withMethod('GET');
        $this->assertFalse($rule($request));
        $request = (new ServerRequest())
            ->withUri(new Uri('https://example.com/v1/api'))
            ->withMethod('GET');
        $this->assertTrue($rule($request));
        $request = (new ServerRequest())
            ->withUri(new Uri('https://example.com/v1//api'))
            ->withMethod('GET');
        $this->assertTrue($rule($request));
        $request = (new ServerRequest())
            ->withUri(new Uri('https://example.com/v1//////api'))
            ->withMethod('GET');
        $this->assertTrue($rule($request));
        $request = (new ServerRequest())
            ->withUri(new Uri('https://example.com//v1/api'))
            ->withMethod('GET');
        $this->assertTrue($rule($request));
        $request = (new ServerRequest())
            ->withUri(new Uri('https://example.com//////v1/api'))
            ->withMethod('GET');
        $this->assertTrue($rule($request));
    }
}
