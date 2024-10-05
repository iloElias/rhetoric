<?php

namespace Ilias\Rhetoric\Abstract;

use Ilias\Opherator\Request\Method;
use Ilias\Rhetoric\Exceptions\InvalidActionClassException;
use Ilias\Rhetoric\Exceptions\InvalidActionMethodException;
use Ilias\Rhetoric\Exceptions\InvalidMiddlewareException;
use Ilias\Rhetoric\Middleware\IMiddleware;

abstract class AbstractRouteHandler
{
  protected function validateMethod(string $method)
  {
    $methodEnum = new Method($method);
    return $methodEnum->getMethod();
  }

  protected function prepareUri(string $uri)
  {
    $uri = trim($uri);
    return $uri;
  }

  protected function validateAction(string $action)
  {
    [$class, $method] = explode('@', $action);
    if (!class_exists($class)) {
      throw new InvalidActionClassException("Class $class does not exist.");
    }

    if (!method_exists($class, $method)) {
      throw new InvalidActionMethodException("Method $method does not exist in class $class.");
    }

    return $action;
  }

  protected function validateMiddlewareArray(array $middlewares)
  {
    foreach ($middlewares as $middleware) {
      if (!is_subclass_of($middleware, IMiddleware::class)) {
        throw new InvalidMiddlewareException("Middleware $middleware must implement IMiddleware.");
      }
    }
    return $middlewares;
  }
}
