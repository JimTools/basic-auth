# Custom Authentication Methods

Sometimes passing users in an array is not enough. To authenticate against custom datasource you can pass a callable as
`authenticator` parameter. This can be either a class which implements AuthenticatorInterface or anonymous function.
Callable receives an array containing `user` and `password` as argument. In both cases authenticator must return either
`true` or `false`.

If you are creating an Enterprise&trade; software which randomly lets people log in you could use the following.

```php
use Tuupola\Middleware\HttpBasicAuthentication\AuthenticatorInterface;
use Tuupola\Middleware\HttpBasicAuthentication;

class RandomAuthenticator implements AuthenticatorInterface {
    public function __invoke(array $arguments): bool {
        return (bool)rand(0,1);
    }
}

$app = new Slim\App;

$app->add(new HttpBasicAuthentication([
    "path" => "/admin",
    "realm" => "Protected",
    "authenticator" => new RandomAuthenticator
]));
```

Same thing can also be accomplished with anonymous function.

```php
$app = new Slim\App;

$app->add(new Tuupola\Middleware\HttpBasicAuthentication([
    "path" => "/admin",
    "realm" => "Protected",
    "authenticator" => function ($arguments) {
        return (bool)rand(0,1);
    }
]));
```

## Setting response body when authentication fails

By default plugin returns an empty response body with 401 response. You can return custom body using by providing an
error handler. This is useful for example when you need additional information why authentication failed.

```php
$app = new Slim\App;

$app->add(new Tuupola\Middleware\HttpBasicAuthentication([
    "path" => "/api",
    "realm" => "Protected",
    "users" => [
        "root" => "t00r",
        "somebody" => "passw0rd"
    ],
    "error" => function ($response, $arguments) {
        $data = [];
        $data["status"] = "error";
        $data["message"] = $arguments["message"];

        $body = $response->getBody();
        $body->write(json_encode($data, JSON_UNESCAPED_SLASHES));

        return $response->withBody($body);
    }
]));
```

## Usage with PDO

For those in hurry there is a ready made PDO authenticator. It covers most of the use cases. You probably end up
implementing your own though.

```php
use Tuupola\Middleware\HttpBasicAuthentication\PdoAuthenticator;

$pdo = new PDO("sqlite:/tmp/users.sqlite");
$app = new Slim\App;

$app->add(new Tuupola\Middleware\HttpBasicAuthentication([
    "path" => "/admin",
    "realm" => "Protected",
    "authenticator" => new PdoAuthenticator([
        "pdo" => $pdo
    ])
]));
```

For better explanation
see [Basic Authentication from Database](http://www.appelsiini.net/2014/slim-database-basic-authentication) blog post.

## Usage with FastCGI

By default Apache [does not pass credentials](https://bugs.php.net/bug.php?id=35752) to FastCGI process. If you are
using mod_fcgi you can configure authorization headers with:

```
FastCgiExternalServer /usr/lib/cgi-bin/php5-fcgi -host 127.0.0.1:9000 -pass-header Authorization
```
