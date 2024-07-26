<?php

namespace Ilias\Rhetoric\Exceptions;

/**
 * Class MiddlewareException
 * Thrown when middleware does not run successfully.
 */
class MiddlewareException extends \Exception
{
  protected $message = 'Middleware execution failed';
}
