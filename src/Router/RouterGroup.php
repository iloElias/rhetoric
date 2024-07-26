<?php

namespace Ilias\Rhetoric\Router;

/**
 * Class RouterGroup
 * Represents a group of routes with a common prefix and middleware.
 */
class RouterGroup
{
  /**
   * @var string The prefix for the group of routes.
   */
  private string $prefix;

  /**
   * @var array The middleware to be applied to the group of routes.
   */
  private array $middleware;

  /**
   * @var array The routes within the group.
   */
  private array $routes = [];

  /**
   * RouterGroup constructor.
   *
   * @param string $prefix The prefix for the group of routes.
   * @param array $middleware The middleware to be applied to the group.
   */
  public function __construct(string $prefix, array $middleware = [])
  {
    $this->prefix = rtrim($prefix, '/');
    $this->middleware = $middleware;
  }

  /**
   * Returns the routes within the group.
   *
   * @return array The routes within the group.
   */
  public function getRoutes()
  {
    return $this->routes;
  }

  /**
   * Adds a route to the group.
   *
   * @param Route $route The route to be added.
   */
  public function addRoute(Route $route)
  {
    $route->uri = $this->prefix . '/' . ltrim($route->uri, '/');
    $route->middleware = array_merge($this->middleware, $route->middleware);
    $this->routes[] = $route;
  }

  /**
   * Registers a GET route within the group.
   *
   * @param string $uri The URI of the route.
   * @param string $action The action associated with the route.
   * @param array $middleware The middleware to be applied to the route.
   */
  public function get(string $uri, string $action, array $middleware = [])
  {
    $this->addRoute(
      new Route('GET', $uri, $action, $middleware)
    );
  }

  /**
   * Registers a POST route within the group.
   *
   * @param string $uri The URI of the route.
   * @param string $action The action associated with the route.
   * @param array $middleware The middleware to be applied to the route.
   */
  public function post(string $uri, string $action, array $middleware = [])
  {
    $this->addRoute(
      new Route('POST', $uri, $action, $middleware)
    );
  }

  /**
   * Registers a PUT route within the group.
   *
   * @param string $uri The URI of the route.
   * @param string $action The action associated with the route.
   * @param array $middleware The middleware to be applied to the route.
   */
  public function put(string $uri, string $action, array $middleware = [])
  {
    $this->addRoute(
      new Route('PUT', $uri, $action, $middleware)
    );
  }

  /**
   * Registers a PATCH route within the group.
   *
   * @param string $uri The URI of the route.
   * @param string $action The action associated with the route.
   * @param array $middleware The middleware to be applied to the route.
   */
  public function patch(string $uri, string $action, array $middleware = [])
  {
    $this->addRoute(
      new Route('PATCH', $uri, $action, $middleware)
    );
  }

  /**
   * Registers a DELETE route within the group.
   *
   * @param string $uri The URI of the route.
   * @param string $action The action associated with the route.
   * @param array $middleware The middleware to be applied to the route.
   */
  public function delete(string $uri, string $action, array $middleware = [])
  {
    $this->addRoute(
      new Route('DELETE', $uri, $action, $middleware)
    );
  }

  /**
   * Registers a custom HTTP method route within the group.
   *
   * @param string $method The HTTP method of the route.
   * @param string $uri The URI of the route.
   * @param string $action The action associated with the route.
   * @param array $middleware The middleware to be applied to the route.
   */
  public function custom(string $method, string $uri, string $action, array $middleware = [])
  {
    $this->addRoute(
      new Route(strtoupper($method), $uri, $action, $middleware)
    );
  }

  /**
   * Registers a nested group of routes within the current group.
   *
   * @param string $prefix The prefix for the nested group of routes.
   * @param callable $callback The callback function to define the nested group routes.
   * @param array $middleware The middleware to be applied to the nested group.
   */
  public function group(string $prefix, callable $callback, array $middleware = [])
  {
    $group = new RouterGroup($this->prefix . '/' . trim($prefix, '/'), array_merge($this->middleware, $middleware));

    call_user_func($callback, $group);

    foreach ($group->getRoutes() as $route) {
      $this->routes[] = $route;
    }
  }
}
