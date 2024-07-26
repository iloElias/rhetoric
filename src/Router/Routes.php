<?php

namespace Ilias\Rhetoric\Router;

class Routes
{
  /**
   * Initializes the routing setup by implementing the registered routes in the `routes.php` file.
   * @return void
   */
  public static function setup()
  {
    $phpRouterFilePath = $_SERVER["DOCUMENT_ROOT"] . DIRECTORY_SEPARATOR . "router.php";
    if (file_exists($phpRouterFilePath)) {
      require_once $phpRouterFilePath;
    }
  }
}
