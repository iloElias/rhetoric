<?php

namespace Ilias\Rhetoric\Router;

use Ilias\Choir\Exceptions\DuplicatedRouteException;
use Ilias\Rhetoric\Exceptions\DuplicatedParameterException;
use Ilias\Rhetoric\Exceptions\MethodNotAllowedException;
use Ilias\Rhetoric\Exceptions\RouteNotFoundException;
use Ilias\Rhetoric\Middleware\IMiddleware;

class Router
{
  private static array $routes = [];
  private static array $params = [];
  private static array $baseMiddleware = [];

  public static function setup()
  {
    $uri = explode("?", $_SERVER["REQUEST_URI"])[0];
    $method = $_SERVER["REQUEST_METHOD"];
    self::dispatch($method, ($uri !== "/" && str_ends_with($uri, "/")) ? substr($uri, 0, -1) : $uri);
  }

  private static function setRoute(Route $route)
  {
    foreach (self::$routes as $storedRoute) {
      if ($storedRoute->uri === $route->uri && $storedRoute->method === $route->method) {
        throw new DuplicatedRouteException("{$route->uri} route with method {$route->method} is already defined");
      }
    }
    self::$routes[] = $route;
  }

  private static function addRoute(Route $route)
  {
    if (str_ends_with($route->uri, "/") && $route->uri !== "/") {
      $route->uri = substr($route->uri, 0, -1);
    }
    self::validateUri($route->uri);
    self::checkForDuplicateRoute($route);
    self::setRoute($route);
  }

  private static function validateUri(string $uri)
  {
    $regex = '/\{([\w]+)\}/';
    preg_match_all($regex, $uri, $paramNames);
    $paramNames = $paramNames[1];
    $paramCounts = array_count_values($paramNames);
    foreach ($paramCounts as $name => $count) {
      if ($count > 1) {
        throw new DuplicatedParameterException("Duplicate parameter '{$name}' found in the route URL.");
      }
    }
  }

  private static function checkForDuplicateRoute(Route $newRoute)
  {
    foreach (self::$routes as $route) {
      if ($route->uri === $newRoute->uri && $route->method === $newRoute->method) {
        throw new DuplicatedRouteException("Duplicated route '{$newRoute->uri}' found for method '{$newRoute->method}'.");
      }
    }
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

  public static function custom(string $method, string $uri, string $action, array $middleware = [])
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

  public static function getParams()
  {
    return self::$params;
  }

  public static function group(string $prefix, callable $callback, array $middleware = [])
  {
    $group = new RouterGroup($prefix, array_merge(self::$baseMiddleware, $middleware));

    call_user_func($callback, $group);

    foreach ($group->getRoutes() as $route) {
      if (str_ends_with($route->uri, "/") && $route->uri !== "/") {
        $route->uri = substr($route->uri, 0, -1);
      }
      self::validateUri($route->uri);
      self::checkForDuplicateRoute($route);
      self::setRoute($route);
    }
  }

  public static function dispatch($method, $uri)
  {
    $routeFound = false;

    foreach (self::$routes as $route) {
      if (self::matchRoute($route, $uri)) {
        $routeFound = true;
        if ($route->method === $method) {
          self::handleRoute($route);
          return;
        }
      }
    }

    if ($routeFound) {
      throw new MethodNotAllowedException("Method not allowed for this route.");
    }
    throw new RouteNotFoundException("Route not found.");
  }

  private static function matchRoute($route, $uri)
  {
    $regex = '/\{([\w]+)\}/';
    $pattern = preg_replace($regex, '([\w-]+)', $route->uri);
    $pattern = str_replace('/', '\/', $pattern);
    $pattern = '/^' . $pattern . '$/';

    if (preg_match($pattern, $uri, $matches)) {
      $params = [];
      preg_match_all($regex, $route->uri, $paramNames);
      foreach ($paramNames[1] as $index => $name) {
        $params[$name] = $matches[$index + 1];
      }
      self::$params = $params;
      return true;
    }

    return false;
  }

  private static function handleRoute(Route $route)
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
