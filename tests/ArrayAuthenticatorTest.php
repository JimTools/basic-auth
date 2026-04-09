<?php

declare(strict_types=1);

/*

Copyright (c) 2013-2024 Mika Tuupola

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.

*/

/**
 * @see       https://github.com/tuupola/slim-basic-auth
 *
 * @license   https://www.opensource.org/licenses/mit-license.php
 */

namespace Tuupola\Middleware\HttpBasicAuthentication;

use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class ArrayAuthenticatorTest extends TestCase
{
    public function testShouldReturnTrue()
    {
        $authenticator = new ArrayAuthenticator([
            'users' => [
                'root' => 't00r',
                'somebody' => 'passw0rd',
                'wheel' => '$2y$10$Tm03qGT4FLqobzbZcfLDcOVIwZEpg20QZYffleeA2jfcClLpufYpy',
                'dovecot' => '$2y$12$BlC21Ah2CuO7xlplqyysEejr1rwnj.uh2IEW9TX0JPgTnLNJk6XOC',
            ],
        ]);
        self::assertTrue($authenticator([
            'user' => 'root',
            'password' => 't00r',
        ]));
        self::assertTrue($authenticator([
            'user' => 'somebody',
            'password' => 'passw0rd',
        ]));
        self::assertTrue($authenticator([
            'user' => 'wheel',
            'password' => 'gashhash',
        ]));
        self::assertTrue($authenticator([
            'user' => 'dovecot',
            'password' => 'prettyfly',
        ]));
    }

    public function testShouldReturnFalse()
    {
        $authenticator = new ArrayAuthenticator([
            'users' => [
                'root' => 't00r',
                'somebody' => 'passw0rd',
                'luser' => '$2y$10$Tm03qGT4FLqobzbZcfLDcOVIwZEpg20QZYffleeA2jfcClLpufYpy',
            ],
        ]);
        self::assertFalse($authenticator([
            'user' => 'root',
            'password' => 'nosuch',
        ]));
        self::assertFalse($authenticator([
            'user' => 'nosuch',
            'password' => 'nosuch',
        ]));

        // Should handle as hash and not cleartext
        self::assertFalse($authenticator([
            'user' => 'luser',
            'password' => '$2y$10$Tm03qGT4FLqobzbZcfLDcOVIwZEpg20QZYffleeA2jfcClLpufYpy',
        ]));
    }
}
