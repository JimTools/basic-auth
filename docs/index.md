# Basic-Auth

This middleware implements [HTTP Basic Authentication](https://en.wikipedia.org/wiki/Basic_access_authentication). It
was originally developed for Slim but can be used with all frameworks using PSR-7 or PSR-15 style middlewares. It has
been tested with [Slim Framework](http://www.slimframework.com/)
and [Zend Expressive](https://zendframework.github.io/zend-expressive/).

::: info
This package is a maintained version of a [tuupola/slim-basic-auth](https://github.com/tuupola/slim-basic-auth/tree/3.x), version `v1.x` is forked from 3.x and
will remain 100% backward compatible with the original library.
:::

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
