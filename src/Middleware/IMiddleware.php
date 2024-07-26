<?php

namespace Ilias\Rhetoric\Middleware;

/**
 * Interface IMiddleware
 * Defines the interface for middleware classes.
 */
interface IMiddleware
{
  /**
   * Handles the middleware logic.
   */
  public static function handle();
}
