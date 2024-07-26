<?php

namespace Ilias\Rhetoric\Exceptions;

use Exception;

/**
 * Class MethodNotAllowedException
 * Thrown when the HTTP method is not allowed for the matched route.
 */
class MethodNotAllowedException extends Exception
{
  protected $message = 'HTTP method not allowed for this route.';
}
