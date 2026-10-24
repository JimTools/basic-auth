<?php

namespace JimTools\BasicAuth;

use Laminas\Diactoros\Response;
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
    public function testShouldNotAuthenticateOptions()
    {
        $request = (new ServerRequest())
            ->withUri(new Uri('https://example.com/api'))
            ->withMethod('OPTIONS');

        $response = new Response();
        $rule = new RequestMethodRule();

        $this->assertFalse($rule($request));
    }

    public function testShouldAuthenticatePost()
    {
        $request = (new ServerRequest())
            ->withUri(new Uri('https://example.com/api'))
            ->withMethod('POST');

        $response = new Response();
        $rule = new RequestMethodRule();

        $this->assertTrue($rule($request));
    }

    public function testShouldAuthenticateGet()
    {
        $request = (new ServerRequest())
            ->withUri(new Uri('https://example.com/api'))
            ->withMethod('GET');

        $response = new Response();
        $rule = new RequestMethodRule();

        $this->assertTrue($rule($request));
    }

    public function testShouldConfigureIgnore()
    {
        $request = (new ServerRequest())
            ->withUri(new Uri('https://example.com/api'))
            ->withMethod('GET');

        $response = new Response();
        $rule = new RequestMethodRule();

        $rule = new RequestMethodRule([
            'ignore' => ['GET'],
        ]);

        $this->assertFalse($rule($request));
    }
}
