# Fenrir Framework - Authentication

## Requirements

- php 8.2+

## Installation

Run the command:
```bash
$ composer require fenrir-soft/authentication
```

Add  the following to your .env file
```
JWT_SECRET=you-secret-phrase
JWT_ALG=HS256
```

Create a src/container-definitions.php file, if you have'nt yet, and add the following:
```php
<?php

use Fenrir\Framework\MiddlewareCollection;
use Rocca\Cdn\Middlewares\AuthMiddleware;
use Rocca\Cdn\Services\JwtService;

return [
    ...
    JwtService::class => function () {
        return new JwtService(
            key: $_ENV['JWT_SECRET'],
            alg: $_ENV['JWT_ALG']
        );
    },
    MiddlewareCollection::class => function () {
        return new MiddlewareCollection(
            AuthMiddleware::class
        );
    },
    ...
]

```
On your controllers add the #Auth attribute

```php

class MyController {

    #[Route(path: '/admin')]
    #[Auth(roles: ["admin"], permissions: ["admin:access"], redirect_url: "admin/login")]
    public function index() {}
}

```
To authenticate you'll need to set the Authorization header with a jwt token with the following claims
```json
{
    "role": "admin",
    "acl": ["admin:access"]
}
```
To create a jwt token you can use the JwtService like this:
```php
<?php

use Fenrir\Framework\Lib\Request;
use Fenrir\Framework\Lib\Response;
use Fenrir\Authentication\Services\JwtService;


class AuthController {
    public function __construct(
        private Request $request,
        private Response $response,
        private JwtService $jwt_service
    ) {}

    public function login() {
        $login = $this->request->get('login', '');
        $password = $this->request->get('password', '');

        if ($login === 'admin' && $password === '12345') {
            $jwt_token = $this->jwt_service->encode([
                'sub' => 'my-user-id',
                'iat' => time(),
                'exp' => time() + 3600, /// valid for one hour
                'role' => 'admin',
                'acl' => ['admin:access']
            ]);

            $this->response->json([
                'jwt' => $jwt_token,                
            ]);
            return;
        }

        $this->response->json([
            'error' => "Invalide username or password"
        ]);
    }
}