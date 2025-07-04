<?php
require_once BASE . "/router.php";
require_once BASE . "/middleware/common.middleware.php";

class Router extends BaseRouter {
}

$route = new Router();
switch ($_SERVER["REQUEST_METHOD"]) {
    case 'GET':
        $route->get('/inventory', "Inventory@getInventory");
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
