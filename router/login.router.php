<?php
require_once BASE . "/router.php";
class Router extends BaseRouter {
}
$route = new Router();
switch ($_SERVER["REQUEST_METHOD"]) {
    case 'GET':
		$route->get('/login', "Login@loginPage");
		$route->get('/login/signOut', "Login@signOut");
		$route->get('/login/changeUser', "Login@changeUser");
		break;
	case 'POST':
		$route->post('/login/signIn', "Login@signIn");
		break;
	case 'PUT':
    	break;
    case 'DELETE':
    	break;
    default:    
    	break;
}
