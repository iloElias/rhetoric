<?php

namespace Ilias\Rhetoric\Exceptions;

use Exception;

/**
 * Class DuplicateRouteException
 * Thrown when a duplicate route is found.
 */
class DuplicatedRouteException extends Exception
{
    protected $message = 'Duplicate route found.';
}
