<?php 
session_start();

require_once("vendor/autoload.php");

use \Slim\Slim;
use \Jsilva\Page;
use \Jsilva\PageAdmin;
use \Jsilva\Model\User;
use \Jsilva\Model\Category;

$app = new Slim();

$app->config('debug', true);

require_once("functions.php");
require_once("site.php");
require_once("admin.php");
require_once("admin-users.php");
require_once("admin-categories.php");
require_once("admin-products.php");
require_once("admin-orders.php");

$app->run();

 ?>