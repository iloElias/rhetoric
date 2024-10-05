<?php

namespace Ilias\Rhetoric\Router;

use Ilias\Rhetoric\Exceptions\DuplicatedRouteException;
use Ilias\Rhetoric\Exceptions\DuplicatedParameterException;
use Ilias\Rhetoric\Exceptions\MethodNotAllowedException;
use Ilias\Rhetoric\Exceptions\RouteNotFoundException;
use Ilias\Rhetoric\Middleware\IMiddleware;

/**
 * Class Router
 * Handles the routing logic for the application, including adding routes, dispatching requests,
 * and handling middleware.
 */
class Router
{
  /**
   * @var array Stores the routes registered in the application.
   */
  private static array $routes = [];

  /**
   * @var array Stores the parameters extracted from the URL.
   */
  private static array $params = [];

  /**
   * @var array Stores the base middleware applied to all routes.
   */
  private static array $baseMiddleware = [];

  /**
   * Initializes the routing setup by dispatching the current request URI.
   * @return mixed
   */
  public static function handle(): mixed
  {
    $uri = explode("?", $_SERVER["REQUEST_URI"])[0];
    $method = $_SERVER["REQUEST_METHOD"];
    return self::dispatch($method, ($uri !== "/" && str_ends_with($uri, "/")) ? substr($uri, 0, -1) : $uri);
  }

  /**
   * @deprecated This function is deprecated and should not be used in new code.
   * Please use `Router::handle()` method instead.
   */
  public static function setup(): mixed
  {
    return self::handle();
  }

  private static function setRoute(Route $route): void
  {
    foreach (self::$routes as $storedRoute) {
      if ($storedRoute->uri === $route->uri && $storedRoute->method === $route->method) {
        throw new DuplicatedRouteException("{$route->uri} route with method {$route->method} is already defined");
      }
    }
    self::$routes[] = $route;
  }

  /**
   * Adds a route to the routing table.
   *
   * @param Route $route The route to be added.
   * @throws DuplicatedRouteException If a route with the same URI and method already exists.
   * @return void
   */
  private static function addRoute(Route $route): void
  {
    if (str_ends_with($route->uri, "/") && $route->uri !== "/") {
      $route->uri = substr($route->uri, 0, -1);
    }
    self::validateUri($route->uri);
    self::checkForDuplicateRoute($route);
    self::setRoute($route);
  }

  /**
   * Validates the URI to ensure there are no duplicate parameters.
   *
   * @param string $uri The URI to be validated.
   * @throws DuplicatedParameterException If duplicate parameters are found in the URI.
   * @return void
   */
  private static function validateUri(string $uri): void
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

  /**
   * Checks if a route with the same URI and method already exists.
   *
   * @param Route $newRoute The new route to be checked.
   * @throws DuplicatedRouteException If a duplicate route is found.
   * @return void
   */
  private static function checkForDuplicateRoute(Route $newRoute): void
  {
    foreach (self::$routes as $route) {
      if ($route->uri === $newRoute->uri && $route->method === $newRoute->method) {
        throw new DuplicatedRouteException("Duplicated route '{$newRoute->uri}' found for method '{$newRoute->method}'.");
      }
    }
  }

  /**
   * Registers a GET route.
   *
   * @param string $uri The URI of the route.
   * @param string $action The action associated with the route.
   * @param array $middleware The middleware list to be applied to the route.
   * @return void
   */
  public static function get(string $uri, string $action, array $middleware = []): void
  {
    self::addRoute(
      new Route('GET', $uri, $action, $middleware)
    );
  }

  /**
   * Registers a POST route.
   *
   * @param string $uri The URI of the route.
   * @param string $action The action associated with the route.
   * @param array $middleware The middleware to be applied to the route.
   * @return void
   */
  public static function post(string $uri, string $action, array $middleware = []): void
  {
    self::addRoute(
      new Route('POST', $uri, $action, $middleware)
    );
  }

  /**
   * Registers a PUT route.
   *
   * @param string $uri The URI of the route.
   * @param string $action The action associated with the route.
   * @param array $middleware The middleware to be applied to the route.
   * @return void
   */
  public static function put(string $uri, string $action, array $middleware = []): void
  {
    self::addRoute(
      new Route('PUT', $uri, $action, $middleware)
    );
  }

  /**
   * Registers a PATCH route.
   *
   * @param string $uri The URI of the route.
   * @param string $action The action associated with the route.
   * @param array $middleware The middleware to be applied to the route.
   * @return void
   */
  public static function patch(string $uri, string $action, array $middleware = []): void
  {
    self::addRoute(
      new Route('PATCH', $uri, $action, $middleware)
    );
  }

  /**
   * Registers a DELETE route.
   *
   * @param string $uri The URI of the route.
   * @param string $action The action associated with the route.
   * @param array $middleware The middleware to be applied to the route.
   * @return void
   */
  public static function delete(string $uri, string $action, array $middleware = []): void
  {
    self::addRoute(
      new Route('DELETE', $uri, $action, $middleware)
    );
  }

  /**
   * Registers a custom HTTP method route.
   *
   * @param string $method The HTTP method of the route.
   * @param string $uri The URI of the route.
   * @param string $action The action associated with the route.
   * @param array $middleware The middleware to be applied to the route.
   * @return void
   */
  public static function custom(string $method, string $uri, string $action, array $middleware = []): void
  {
    self::addRoute(
      new Route(strtoupper($method), $uri, $action, $middleware)
    );
  }

  /**
   * Returns a list of all registered routes.
   *
   * @return array The list of registered routes.
   */
  public static function getRoutesAvailable(): array
  {
    $routes = [];
    foreach (self::$routes as $route) {
      $routes[] = $route->uri;
    }

    return $routes;
  }

  /**
   * Returns a list of all route params that were found.
   *
   * @return array The list of the route params.
   */
  public static function getParams()
  {
    return self::$params;
  }

  /**
   * Registers a group of routes with a common prefix and middleware.
   *
   * @param string $prefix The prefix for the group of routes.
   * @param callable $callback The callback function to define the group routes.
   * @param array $middleware The middleware list to be applied to the group.
   * @return void
   */
  public static function group(string $prefix, callable $callback, array $middleware = []): void
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

  /**
   * Dispatches a request to the appropriate route.
   *
   * @param string $method The HTTP method of the request.
   * @param string $uri The URI of the request.
   * @throws MethodNotAllowedException If the method is not allowed for the matched route.
   * @throws RouteNotFoundException If no matching route is found.
   * @return void
   */
  public static function dispatch($method, $uri): mixed
  {
    $routeFound = false;

    foreach (self::$routes as $route) {
      if (self::matchRoute($route, $uri)) {
        $routeFound = true;
        if ($route->method === $method) {
          return self::handleRoute($route);
        }
      }
    }

    if ($routeFound) {
      throw new MethodNotAllowedException("Method not allowed for this route.");
    }
    throw new RouteNotFoundException("Route not found.");
  }

  /**
   * Matches a route against the given URI and extracts parameters.
   *
   * @param Route $route The route to be matched.
   * @param string $uri The URI to match against.
   * @return bool True if the route matches, false otherwise.
   * @return bool
   */
  private static function matchRoute($route, $uri): bool
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

  /**
   * Handles the matched route by executing its middleware and action.
   *
   * @param Route $route The matched route.
   * @return mixed
   */
  private static function handleRoute(Route $route): mixed
  {
    foreach ($route->middleware as $middleware) {
      if (is_subclass_of($middleware, IMiddleware::class)) {
        $middleware::handle();
      }
    }

    [$controller, $method] = explode('@', $route->action);
    $controllerInstance = new $controller;
    return call_user_func([$controllerInstance, $method]);
  }
}
