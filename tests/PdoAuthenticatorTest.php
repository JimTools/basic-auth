<?php

namespace Tuupola\Middleware\HttpBasicAuthentication;

use PDO;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(PdoAuthenticator::class)]
class PdoAuthenticatorTest extends TestCase
{
    public PDO $pdo;

    public function setup(): void
    {
        $this->pdo = new PDO('sqlite::memory:');
        // $this->pdo = new \PDO("sqlite:/tmp/test.db");

        $this->pdo->exec(
            'CREATE TABLE users (
                user VARCHAR(32) NOT NULL,
                hash VARCHAR(255) NOT NULL
            )'
        );

        $user = 'root';
        $hash = password_hash('t00r', PASSWORD_DEFAULT);

        $status = $this->pdo->exec(
            "INSERT INTO users (user, hash) VALUES ('{$user}', '{$hash}')"
        );
    }

    public function testShouldReturnTrue()
    {
        $authenticator = new PdoAuthenticator([
            'pdo' => $this->pdo,
        ]);
        $this->assertTrue($authenticator([
            'user' => 'root',
            'password' => 't00r',
        ]));
    }

    public function testShouldReturnFalse()
    {
        $authenticator = new PdoAuthenticator([
            'pdo' => $this->pdo,
        ]);
        $this->assertFalse($authenticator([
            'user' => 'root',
            'password' => 'nosuch',
        ]));
        $this->assertFalse($authenticator([
            'user' => 'nosuch',
            'password' => 'nosuch',
        ]));
    }

    public function testShouldUseLimit()
    {
        $authenticator = new PdoAuthenticator([
            'pdo' => $this->pdo,
        ]);

        $this->assertEquals(
            'SELECT * FROM users WHERE user = ? LIMIT 1',
            $authenticator->sql()
        );
    }

    public function testShouldUseTop()
    {
        // Workaround to test without sqlsrv with
        define('__PHPUNIT_ATTR_DRIVER_NAME__', 'sqlsrv');

        $authenticator = new PdoAuthenticator([
            'pdo' => $this->pdo,
        ]);
        $this->assertEquals(
            'SELECT TOP 1 * FROM users WHERE user = ?',
            $authenticator->sql()
        );
    }
}
