<?php

namespace JimTools\BasicAuth\Tests;

use JimTools\BasicAuth\Authentication\ArrayAuthenticator;
use JimTools\BasicAuth\User;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(ArrayAuthenticator::class)]
class ArrayAuthenticatorTest extends TestCase
{
    #[DataProvider('trueProvider')]
    public function testShouldReturnTrue(): void
    {
        $authenticator = new ArrayAuthenticator([
            new User('root', 't00r'),
            new User('somebody', 'passw0rd'),
            new User('wheel', '$2y$10$Tm03qGT4FLqobzbZcfLDcOVIwZEpg20QZYffleeA2jfcClLpufYpy'),
            new User('dovecot', '$2y$12$BlC21Ah2CuO7xlplqyysEejr1rwnj.uh2IEW9TX0JPgTnLNJk6XOC'),
        ]);

        self::assertTrue($authenticator('root', 't00r'));
        self::assertTrue($authenticator('somebody', 'passw0rd'));
        self::assertTrue($authenticator('wheel', 'gashhash'));
        self::assertTrue($authenticator('dovecot', 'prettyfly'));
    }

    /**
     * @return array<string,array{0:string,1:string}>
     */
    public static function trueProvider(): array
    {
        return [
            'root' => ['root', 't00r'],
            'somebody' => ['somebody', 'passw0rd'],
            'wheel' => ['wheel', 'gashhash'],
            'dovecot' => ['dovecot', 'prettyfly'],
        ];
    }

    #[DataProvider('falseProvider')]
    public function testShouldReturnFalse(?string $username, ?string $password): void
    {
        $authenticator = new ArrayAuthenticator([
            new User('root', 't00r'),
            new User('somebody', 'passw0rd'),
            new User('luser', '$2y$10$Tm03qGT4FLqobzbZcfLDcOVIwZEpg20QZYffleeA2jfcClLpufYpy'),
        ]);

        self::assertFalse($authenticator($username, $password));
    }

    /**
     * @return array<string,array{0:null|string,1:null|string}>
     */
    public static function falseProvider(): array
    {
        return [
            'root' => ['root', 'nosuch'],
            'somebody' => ['nosuch', 'nosuch'],
            'luser' => ['luser', '$2y$10$Tm03qGT4FLqobzbZcfLDcOVIwZEpg20QZYffleeA2jfcClLpufYpy'],
            'null' => [null, null],
        ];
    }
}
