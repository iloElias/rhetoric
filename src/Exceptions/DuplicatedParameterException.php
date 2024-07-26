<?php

namespace Ilias\Rhetoric\Exceptions;

use Exception;

/**
 * Class DuplicateParameterException
 * Thrown when duplicate parameters are found in a route URI.
 */
class DuplicatedParameterException extends Exception
{
    protected $message = 'Duplicate parameter found in the route URL.';
}
