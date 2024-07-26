<?php

namespace Ilias\Rhetoric\Router;

/**
 * Class Route
 * Represents a single route in the routing system.
 */
class Route
{
  /**
   * @var string The HTTP method of the route.
   */
  public string $method;

  /**
   * @var string The URI of the route.
   */
  public string $uri;

  /**
   * @var string The action associated with the route.
   */
  public string $action;

  /**
   * @var array The middleware to be applied to the route.
   */
  public array $middleware;

  /**
   * Route constructor.
   *
   * @param string $method The HTTP method of the route.
   * @param string $uri The URI of the route.
   * @param string $action The action associated with the route.
   * @param array $middleware The middleware to be applied to the route.
   */
  public function __construct(string $method, string $uri, string $action, array $middleware = [])
  {
    $this->method = $method;
    $this->uri = $uri;
    $this->action = $action;
    $this->middleware = $middleware;
  }
}
