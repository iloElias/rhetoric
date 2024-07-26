<?php

namespace Ilias\Rhetoric\Exceptions;

class MethodNotAllowedException extends \Exception
{
  protected $message = 'HTTP method not allowed for this route';
}
