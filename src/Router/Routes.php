<?php

namespace Ilias\Rhetoric\Router;

class Routes
{
  /**
   * Initializes the routing setup by implementing the registered routes in the `routes.php` file.
   * @return void
   * @deprecated This functionality was moved to `Router::setup()`.
   */
  public static function setup()
  {
    trigger_error("The functionality is now deprecated and was moved to `Router::setup()`.", E_USER_NOTICE);
    $phpRouterFilePath = $_SERVER["DOCUMENT_ROOT"] . DIRECTORY_SEPARATOR . "router.php";
    if (file_exists($phpRouterFilePath)) {
      require_once $phpRouterFilePath;
    }
  }
}
