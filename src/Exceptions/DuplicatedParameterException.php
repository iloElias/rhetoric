<?php

namespace Ilias\Rhetoric\Exceptions;

use Exception;

class DuplicatedParameterException extends Exception
{
  protected $message = 'Duplicate parameter found in the route URL.';
}
