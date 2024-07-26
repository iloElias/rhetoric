<?php

namespace Ilias\Rhetoric\Exceptions;

use Exception;

class DuplicateRouteException extends Exception
{
  protected $message = 'Duplicate route found.';
}
