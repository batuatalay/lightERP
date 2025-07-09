<?php
require_once BASE . "/router.php";
class Router extends BaseRouter {
}
$route = new Router();
switch ($_SERVER["REQUEST_METHOD"]) {
    case 'GET':
		$route->get('/user', "User@loginPage");
		break;
	case 'POST':
		break;
	case 'PUT':
		$route->put('/user/create', 'User@CreateUser');
    	break;
    case 'DELETE':
    	break;
    default:    
    	break;
}
