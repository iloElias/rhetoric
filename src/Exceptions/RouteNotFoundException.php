<?php

namespace Ilias\Rhetoric\Exceptions;

use Exception;

/**
 * Class RouteNotFoundException
 * Thrown when no matching route is found.
 */
class RouteNotFoundException extends Exception
{
  protected $message = 'Route not found.';
}
