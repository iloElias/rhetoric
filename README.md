# Rhetoric Router @IloElias

[![Maintainer](http://img.shields.io/badge/maintainer-@iloElias-blue.svg?style=flat-square)](https://github.com/iloElias)
[![Source Code](https://img.shields.io/badge/source-iloelias/rhetoric-blue.svg?style=flat-square)](https://github.com/iloElias/rhetoric)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)

Simple router system for PHP


Sure! Below is a markdown guide for using the PHP router system as a Composer package.

# PHP Router System Package

This PHP router system allows you to define and manage your application's routes in a simple and organized manner, inspired by Laravel's routing system.

## Usage

### Step 1: Define Your Routes

Create a file to define your routes, for example, in your project root folder, `routes.php`:

```php
<?php

Router::get("/", IndexController::class . "@handleApiIndex");
Router::get("/favicon.ico", IndexController::class . "@favicon");

Router::get("/asset", AssetController::class . "@instruction");

Router::group(['prefix' => '/asset'], function ($router) {
  $router->group(['prefix' => '/type/{type}'], function ($router) {
    $router->get("/name/{name}", AssetController::class . "@getAssetByName");
    $router->get("/id/{id}", AssetController::class . "@getAssetById");
  });
});

Router::get("/debug", DebugController::class . "@showEnvironment");
```

### Step 2: Set Up Your Router

In your application's entry point, typically `index.php`, set up the router to handle incoming requests:

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/routes.php';

use Ilias\PhpHttpRequestHandler\Router\Router;

Router::setup();
```

### Step 3: Create Controllers

Create your controller classes to handle the requests. For example, create `IndexController.php`:

```php
<?php

namespace Ilias\PhpHttpRequestHandler\Controller;

class IndexController
{
  public function handleApiIndex()
  {
    echo "Welcome to the API!";
  }

  public function favicon()
  {
    // Handle favicon request
  }
}
```

Similarly, create other controller classes like `AssetController.php` and `DebugController.php` as needed.

### Step 4: Handling Middleware (Optional)

If you want to use middleware, create a middleware class implementing `Ilias\PhpHttpRequestHandler\Middleware\Middleware`:

```php
<?php

namespace Ilias\PhpHttpRequestHandler\Middleware;

class ExampleMiddleware implements Middleware
{
  public static function handle()
  {
    // Middleware logic here
  }
}
```

Then, apply middleware to your routes or route groups:

```php
Router::get("/protected", IndexController::class . "@protectedMethod", [ExampleMiddleware::class]);

Router::group(['prefix' => '/admin', 'middleware' => [ExampleMiddleware::class]], function ($router) {
  $router->get("/dashboard", AdminController::class . "@dashboard");
});
```

### Step 5: Testing Your Routes

Start your PHP server and navigate to the defined routes to test them:

```bash
php -S localhost:8000
```

Visit `http://localhost:8000` to see your API in action.

## Conclusion

This PHP router system provides a simple and flexible way to define and manage your application's routes. By following the steps above, you can easily set up and use this package in your project.