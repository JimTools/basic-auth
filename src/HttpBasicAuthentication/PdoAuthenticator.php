<?php

declare(strict_types=1);

namespace Tuupola\Middleware\HttpBasicAuthentication;

use PDO;

final class PdoAuthenticator implements AuthenticatorInterface
{
    /**
     * Stores all the options passed to the authenticator.
     *
     * @var mixed[]
     */
    private array $options;

    /**
     * @param mixed[] $options
     */
    public function __construct(array $options = [])
    {
        // Default options.
        $this->options = [
            'table' => 'users',
            'user' => 'user',
            'hash' => 'hash',
        ];

        if ($options) {
            $this->options = array_merge($this->options, $options);
        }
    }

    /**
     * @param string[] $arguments
     */
    public function __invoke(array $arguments): bool
    {
        $user = $arguments['user'];
        $password = $arguments['password'];

        $sql = $this->sql();

        $statement = $this->options['pdo']->prepare($sql);
        $statement->execute([$user]);

        if ($user = $statement->fetch(PDO::FETCH_ASSOC)) {
            return password_verify($password, $user[$this->options['hash']]);
        }

        return false;
    }

    public function sql(): string
    {
        $driver = $this->options['pdo']->getAttribute(PDO::ATTR_DRIVER_NAME);

        // Workaround to test without sqlsrv with
        if (defined('__PHPUNIT_ATTR_DRIVER_NAME__')) {
            $driver = __PHPUNIT_ATTR_DRIVER_NAME__;
        }

        if ('sqlsrv' === $driver) {
            $sql
                = "SELECT TOP 1 *
                 FROM {$this->options['table']}
                 WHERE {$this->options['user']} = ?";
        } else {
            $sql
                = "SELECT *
                 FROM {$this->options['table']}
                 WHERE {$this->options['user']} = ?
                 LIMIT 1";
        }

        return (string) preg_replace('!\s+!', ' ', $sql);
    }
}
