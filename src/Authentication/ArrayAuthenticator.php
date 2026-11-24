<?php

declare(strict_types=1);

namespace JimTools\BasicAuth\Authentication;

use JimTools\BasicAuth\User;
use SensitiveParameter;

final readonly class ArrayAuthenticator implements AuthenticatorInterface
{
    /**
     * @param list<User> $users
     */
    public function __construct(private array $users = []) {}

    public function __invoke(
        #[SensitiveParameter]
        ?string $username,
        #[SensitiveParameter]
        ?string $password
    ): bool {
        foreach ($this->users as $user) {
            if ($user->username !== $username) {
                continue;
            }

            $info = password_get_info($user->password);
            if ($info['algo'] !== null) {
                return password_verify($password, $user->password);
            }

            return hash_equals($user->password, $password);
        }

        return false;
    }
}
