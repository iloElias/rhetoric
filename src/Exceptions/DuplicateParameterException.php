<?php

namespace Ilias\Rhetoric\Exceptions;

use Exception;

class DuplicateParameterException extends Exception
{
  protected $message = 'Duplicate parameter found in the route URL.';
}
