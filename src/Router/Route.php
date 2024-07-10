<?php

namespace Ilias\Rhetoric\Router;

class Route
{
  public string $method;
  public string $uri;
  public string $action;
  public array $middleware;

  public function __construct(string $method, string $uri, string $action, array $middleware = [])
  {
    $this->method = $method;
    $this->uri = $uri;
    $this->action = $action;
    $this->middleware = $middleware;
  }
}
