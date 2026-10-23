# Optional parameters

## Path

The optional `path` parameter allows you to specify the protected part of your website. It can be either a string or an
array. You do not need to specify each URL. Instead think of `path` setting as a folder. In the example below everything
starting with `/api` will be authenticated.

``` php
$app = new Slim\App;

$app->add(new Tuupola\Middleware\HttpBasicAuthentication([
    "path" => "/api", /* or ["/admin", "/api"] */
    "realm" => "Protected",
    "users" => [
        "root" => "t00r",
        "somebody" => "passw0rd"
    ]
]));
```

## Ignore

With optional `ignore` parameter you can make exceptions to `path` parameter. In the example below everything starting
with `/api` and `/admin`  will be authenticated with the exception of `/api/token` and `/admin/ping` which will not be
authenticated.

``` php
$app = new Slim\App;

$app->add(new Tuupola\Middleware\HttpBasicAuthentication([
    "path" => ["/api", "/admin"],
    "ignore" => ["/api/token", "/admin/ping"],
    "realm" => "Protected",
    "users" => [
        "root" => "t00r",
        "somebody" => "passw0rd"
    ]
]));
```

## Before

Before function is called only when authentication succeeds but before the next incoming middleware is called. You can
use this to alter the request before passing it to the next incoming middleware in the stack. If it returns anything
else than `\Psr\Http\Message\RequestInterface` the return value will be ignored.

```php
$app = new Slim\App;

$app->add(new Tuupola\Middleware\HttpBasicAuthentication([
    "path" => "/admin",
    "realm" => "Protected",
    "users" => [
        "root" => "t00r",
        "somebody" => "passw0rd"
    ],
    "before" => function ($request, $arguments) {
        return $request->withAttribute("user", $arguments["user"]);
    }
]));
```

## After

After function is called only when authentication succeeds and after the incoming middleware stack has been called. You
can use this to alter the response before passing it next outgoing middleware in the stack. If it returns anything else
than `\Psr\Http\Message\ResponseInterface` the return value will be ignored.

```php
$app = new Slim\App;

$app->add(new Tuupola\Middleware\HttpBasicAuthentication([
    "path" => "/admin",
    "realm" => "Protected",
    "users" => [
        "root" => "t00r",
        "somebody" => "passw0rd"
    ],
    "after" => function ($response, $arguments) {
        return $response->withHeader("X-Brawndo", "plants crave");
    }
]));
```
