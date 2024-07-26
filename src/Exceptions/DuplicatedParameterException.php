<?php

namespace Ilias\Rhetoric\Exceptions;

class DuplicatedParameterException extends \Exception
{
  protected $message = 'Duplicate parameter found in the route URL.';
}
