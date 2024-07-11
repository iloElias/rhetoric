# Rhetoric Router @IloElias

[![Maintainer](http://img.shields.io/badge/maintainer-@iloElias-blue.svg)](https://github.com/iloElias)
[![Package](https://img.shields.io/badge/package-iloelias/rhetoric-orange.svg)](https://packagist.org/packages/ilias/rhetoric)
[![Source Code](https://img.shields.io/badge/source-iloelias/rhetoric-blue.svg)](https://github.com/iloElias/rhetoric)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg)](LICENSE)

This PHP router system allows you to define and manage your application's routes in a simple and organized manner, inspired by Laravel's routing system.
## Installation

To install the package, add it to your `composer.json` file:

```json
{
  "require": {
    "ilias/rhetoric": "1.0.0"
  }
}
```

Or simply run the terminal command

```bash
composer require ilias/rhetoric
```

Then, run the following command to install the package:

```bash
composer install
```

## Usage

### Step 1: Define Your Routes

Create a file to define your routes, for example, in your project root folder, `routes.php`:

```php
<?php

use Ilias\Rhetoric\Router\Router;

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

use Ilias\Rhetoric\Router\Router;

Router::setup();
```

### Step 3: Create Controllers

Create your controller classes to handle the requests. For example, create `IndexController.php`:

```php
<?php

namespace Ilias\Rhetoric\Controller;

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

If you want to use middleware, create a middleware class implementing `Ilias\Rhetoric\Middleware\Middleware`:

```php
<?php

namespace Ilias\Rhetoric\Middleware;

use Ilias\Rhetoric\Middleware\Middleware;

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

### Step 5: Dispatching Routes

Using the `Request` static method, `dispatch()`, you can handle the current route:

```php
<?php

Request::dispatch($requestMethod, $requestUri);
```


## Explanations

* `::class`

  Is recommended to use the static reference to your class, so te code does know exactly which class to use
