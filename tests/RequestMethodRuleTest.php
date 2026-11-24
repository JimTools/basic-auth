<?php

namespace JimTools\BasicAuth\Tests;

use JimTools\BasicAuth\Rules\RequestMethodRule;
use Laminas\Diactoros\ServerRequest;
use Laminas\Diactoros\Uri;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(RequestMethodRule::class)]
class RequestMethodRuleTest extends TestCase
{
    public function testShouldNotAuthenticateOptions(): void
    {
        $request = (new ServerRequest())
            ->withUri(new Uri('https://example.com/api'))
            ->withMethod('OPTIONS');

        $rule = new RequestMethodRule();

        $this->assertFalse($rule($request));
    }

    public function testShouldAuthenticatePost(): void
    {
        $request = (new ServerRequest())
            ->withUri(new Uri('https://example.com/api'))
            ->withMethod('POST');

        $rule = new RequestMethodRule();

        $this->assertTrue($rule($request));
    }

    public function testShouldAuthenticateGet(): void
    {
        $request = (new ServerRequest())
            ->withUri(new Uri('https://example.com/api'))
            ->withMethod('GET');

        $rule = new RequestMethodRule();

        $this->assertTrue($rule($request));
    }

    public function testShouldConfigureIgnore(): void
    {
        $request = (new ServerRequest())
            ->withUri(new Uri('https://example.com/api'))
            ->withMethod('GET');

        $rule = new RequestMethodRule(['GET']);

        self::assertFalse($rule($request));
    }
}
