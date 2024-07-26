<?php

namespace Ilias\Rhetoric\Exceptions;

class MiddlewareException extends \Exception
{
  protected $message = 'Middleware execution failed';
}
