<?php
require_once BASE . "/router.php";
require_once BASE . "/middleware/common.middleware.php";

class Router extends BaseRouter {
}

$route = new Router();
//TODO :: $_SERVER["REQUEST_METHOD"] check
switch ($_SERVER["REQUEST_METHOD"]) {
    case 'GET':
		$route->get('/main', "Main@getMainPage");
		$route->get('/main/dashboard', "Main@getDashboard");
		$route->get('/main/test', "Main@testFunction");
		break;
	case 'POST':
		break;
	case 'PUT':
    	break;
    case 'DELETE':
    	break;
    default:    
    	break;
}