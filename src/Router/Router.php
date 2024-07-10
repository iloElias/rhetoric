<?php

namespace Ilias\Rhetoric\Router;

use Ilias\Rhetoric\IMiddleware\IMiddleware;

class Router
{
  private static $routes = [];
  private static $baseMiddleware = [];

  public static function setup()
  {
    $uri = explode("?", $_SERVER["REQUEST_URI"])[0];
    $method = $_SERVER["REQUEST_METHOD"];
    self::dispatch($method, ($uri !== "/" && str_ends_with($uri, "/")) ? substr($uri, 0, -1) : $uri);
  }

  private static function addRoute(Route $route)
  {
    if (str_ends_with($route->uri, "/") && $route->uri !== "/") {
      $route->uri = substr($route->uri, 0, -1);
    }
    self::$routes[] = $route;
  }

  public static function get(string $uri, string $action, array $middleware = [])
  {
    self::addRoute(
      new Route('GET', $uri, $action, $middleware)
    );
  }

  public static function post(string $uri, string $action, array $middleware = [])
  {
    self::addRoute(
      new Route('POST', $uri, $action, $middleware)
    );
  }

  public static function put(string $uri, string $action, array $middleware = [])
  {
    self::addRoute(
      new Route('PUT', $uri, $action, $middleware)
    );
  }

  public static function patch(string $uri, string $action, array $middleware = [])
  {
    self::addRoute(
      new Route('PATCH', $uri, $action, $middleware)
    );
  }

  public static function delete(string $uri, string $action, array $middleware = [])
  {
    self::addRoute(
      new Route('DELETE', $uri, $action, $middleware)
    );
  }

  public function custom(string $method, string $uri, string $action, array $middleware = [])
  {
    self::addRoute(
      new Route(strtoupper($method), $uri, $action, $middleware)
    );
  }

  public static function getRoutesAvailable()
  {
    $routes = [];
    foreach (self::$routes as $route) {
      $routes[] = $route->uri;
    }

    return $routes;
  }

  public static function group(string $prefix, callable $callback, array $middleware = [])
  {
    $group = new RouterGroup($prefix, array_merge(self::$baseMiddleware, $middleware));

    call_user_func($callback, $group);

    foreach ($group->getRoutes() as $route) {
      if (str_ends_with($route->uri, "/") && $route->uri !== "/") {
        $route->uri = substr($route->uri, 0, -1);
      }
      self::$routes[] = $route;
    }
  }

  public static function dispatch($method, $uri)
  {
    foreach (self::$routes as $route) {
      if (self::matchRoute($route, $method, $uri)) {
        self::handleRoute($route);
        return;
      }
    }
  }

  private static function matchRoute($route, $method, $uri): array | bool
  {
    if ($route->method !== $method) {
      return false;
    }

    $regex = '/\{([\w]+)\}/';
    $pattern = preg_replace($regex, '([\w-]+)', $route->uri);
    $pattern = str_replace('/', '\/', preg_replace($regex, '([\w-]+)', $route->uri));
    $pattern = '/^' . $pattern . '$/';

    if (preg_match($pattern, $uri, $matches)) {
      $params = [];
      preg_match_all($regex, $route->uri, $paramNames);
      foreach ($paramNames[1] as $index => $name) {
        $params[$name] = $matches[$index + 1];
      }
      return $params;
    }

    return false;
  }

  private static function handleRoute($route)
  {
    foreach ($route->middleware as $middleware) {
      if (is_subclass_of($middleware, IMiddleware::class)) {
        $middleware::handle();
      }
    }

    [$controller, $method] = explode('@', $route->action);
    $controllerInstance = new $controller;
    call_user_func([$controllerInstance, $method]);
  }
}
