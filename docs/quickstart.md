# Quickstart

This page provides a quick introduction to the package and introductory examples. If you have not already installed,
head over to the [Installation](./overview#install) page

## Basic Usage

The below example is the most basic use of the middleware, acting as a global authentication middleware on all routes.

```php
$app = new Slim\App;

$app->add(new Tuupola\Middleware\HttpBasicAuthentication([
    "users" => [
        "root" => "t00r",
        "somebody" => "passw0rd"
    ]
]));
```

Cleartext passwords are only good for quick testing. You probably want to use hashed passwords. Hashed password can be
generated with `htpasswd` command line tool or [password_hash ()](http://php.net/manual/en/function.password-hash.php)
PHP function

```shell
htpasswd -nbBC 10 root t00r
# root:$2y$10$1lwCIlqktFZwEBIppL4ak.I1AHxjoKy9stLnbedwVMrt92aGz82.O

htpasswd -nbBC 10 somebody passw0rd
# somebody:$2y$10$6/vGXuMUoRlJUeDN.bUWduge4GhQbgPkm6pfyGxwgEWT0vEkHKBUW
```

```php
$app = new Slim\App;

$app->add(new Tuupola\Middleware\HttpBasicAuthentication([
    "users" => [
        "root" => '$2y$10$1lwCIlqktFZwEBIppL4ak.I1AHxjoKy9stLnbedwVMrt92aGz82.O',
        "somebody" => '$2y$10$6/vGXuMUoRlJUeDN.bUWduge4GhQbgPkm6pfyGxwgEWT0vEkHKBUW'
    ]
]));
```

Even if you are using hashed passwords it is not the best idea to store credentials in the code. Instead, you could
store them in environment or external file which is not committed to GitHub.

```php
$app = new Slim\App;

$app->add(new Tuupola\Middleware\HttpBasicAuthentication([
    "users" => [
        "admin" => getenv("ADMIN_PASSWORD")
    ]
]));
```
