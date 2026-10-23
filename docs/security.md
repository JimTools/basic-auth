# Security

Basic authentication transmits credentials in clear text. For this reason HTTPS should always be used together with basic authentication. If the middleware detects insecure usage over HTTP it will throw a `RuntimeException` with the following message: `Insecure use of middleware over HTTP denied by configuration`.

By default, localhost is allowed to use HTTP. The security behavior of `HttpBasicAuthentication` can also be configured to allow:

- [a whitelist of domains to connect insecurely](#how-to-configure-a-whitelist)
- [forwarding of an HTTPS connection to HTTP](#allow-https-termination-and-forwarding)
- [all unencrypted traffic](#allow-all-unencrypted-traffic)

### How to configure a whitelist:
You can list hosts to allow access insecurely.  For example, to allow HTTP traffic to your development host `dev.example.com`, add the hostname to the `relaxed` config key.

``` php
$app = new Slim\App;

$app->add(new Tuupola\Middleware\HttpBasicAuthentication([
    "path" => "/admin",
    "secure" => true,
    "relaxed" => ["localhost", "dev.example.com"],
    "users" => [
        "root" => "t00r",
        "somebody" => "passw0rd"
    ]
]));
```
## Allow HTTPS termination and forwarding
If public traffic terminates SSL on a load balancer or proxy and forwards to the application host insecurely, `HttpBasicAuthentication` can inspect request headers to ensure that the original client request was initiated securely.  To enable, add the string `headers` to the `relaxed` config key.

```php
$app = new Slim\App;

$app->add(new Tuupola\Middleware\HttpBasicAuthentication([
    "path" => "/admin",
    "secure" => true,
    "relaxed" => ["localhost", "headers"],
    "users" => [
        "root" => "t00r",
        "somebody" => "passw0rd"
    ]
]));
```

## Allow all unencrypted traffic
To allow insecure usage by any host, you must enable it manually by setting `secure` to `false`. This is generally a bad idea. Use only if you know what you are doing.

``` php
$app = new Slim\App;

$app->add(new Tuupola\Middleware\HttpBasicAuthentication([
    "path" => "/admin",
    "secure" => false,
    "users" => [
        "root" => "t00r",
        "somebody" => "passw0rd"
    ]
]));
```
