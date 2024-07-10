<?php

namespace Ilias\Rhetoric\Router;

class Routes
{
  public static function setup()
  {
    $phpRouterFilePath = $_SERVER["DOCUMENT_ROOT"] . DIRECTORY_SEPARATOR . "router.php";
    if (file_exists($phpRouterFilePath)) {
      require_once $phpRouterFilePath;
    }
  }
}
