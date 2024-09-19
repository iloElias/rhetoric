<?

namespace Ilias\Rhetoric\Exceptions;

class InvalidActionMethodException extends \Exception
{
  protected $message = 'Action method is not present in the action class.';
}
