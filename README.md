# PSR-7 and PSR-15 Basic Auth Middleware

> [!NOTE]
> This package is a maintained version of
a [tuupola/slim-basic-auth](https://github.com/tuupola/slim-basic-auth/tree/3.x),
> version 1 is forked from 3.x and will remained 100% backward compatible with the original library.

This middleware implements [HTTP Basic Authentication](https://en.wikipedia.org/wiki/Basic_access_authentication). It
was originally developed for Slim but can be used with all frameworks using PSR-7 or PSR-15 style middlewares. It has
been tested with [Slim Framework](http://www.slimframework.com/)
and [Zend Expressive](https://zendframework.github.io/zend-expressive/).

[![Latest Version](https://img.shields.io/packagist/v/jimtools/basic-auth.svg?style=flat-square)](https://packagist.org/packages/jimtools/basic-auth)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)
[![Build Status](https://img.shields.io/github/actions/workflow/status/jimtools/basic-auth/tests.yml?branch=main&style=flat-square)](https://github.com/jimtools/basic-auth/actions)
[![Coverage](https://img.shields.io/codecov/c/github/jimtools/basic-auth/main.svg?style=flat-square)](https://codecov.io/gh/jimtools/basic-auth/branch/main)

```php
use Slim\Factory\AppFactory;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Tuupola\Middleware\HttpBasicAuthentication;

require __DIR__ . '/vendor/autoload.php';

$app = AppFactory::create();
$app->addErrorMiddleware(true, true, true);
$app->addMiddleware(new HttpBasicAuthentication([
    'users' => ['cosmo' => 'tooManySecrets'],
]));

$app->get('/', function (Request $request, Response $response) {
    $response->getBody()->write('Hello, ' . $request->getServerParams()['PHP_AUTH_USER']);

    return $response;
});

$app->run();
```

> [!NOTE]
> Heads up! You are reading documentation for [3.x branch](https://github.com/tuupola/slim-basic-auth/tree/3.x) which is PHP 7.1 and up only. If you are using older
> version of PHP see the [2.x branch](https://github.com/tuupola/slim-basic-auth/tree/2.x). These two branches are not
> backwards compatible, see [UPGRADING](https://github.com/tuupola/slim-basic-auth/blob/3.x/UPGRADING.md) for
> instructions how to upgrade.

## Install

```bash
composer require jimtools/basic-auth
composer remove tuupola/slim-basic-auth # migration only
```

## Documentation

GitHub issues are used for only to discuss bugs and new features, for support please use GitHub discussions.

- [Documentation](https://jimtools.github.io/basic-auth/main)
- [Support](https://github.com/JimTools/basic-auth/discussions)
- [Bugs](https://github.com/JimTools/basic-auth/issues)

## Security

If you discover any security related issues, please email [james.read.18@gmail.com](maillto:james.read.18@gmail.com)
instead of using the issue tracker.

## Contributing

Please see [CONTRIBUTING](./CONTRIBUTING.md) for details.

## License

The MIT License (MIT). Please see [LICENSE](./LICENSE) for more information.
