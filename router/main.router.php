<?php
require_once BASE . "/router.php";
require_once BASE . "/middleware/common.middleware.php";

class Router extends BaseRouter {
}

$route = new Router();
//TODO :: $_SERVER["REQUEST_METHOD"] check
$route->get('/main', "Main@getMainPage");
$route->get('/main/dashboard', "Main@getDashboard");