<?php
require_once BASE . "/router.php";
require_once BASE . "/middleware/common.middleware.php";

class Router extends BaseRouter {
}

$route = new Router();
switch ($_SERVER["REQUEST_METHOD"]) {
    case 'GET':
        $route->get('/company', "Inventory@getInventory");
        break;
    case 'POST':
        break;
    case 'PUT':
        $route->put('/company/create', "Company@create");
    break;
    case 'DELETE':
        $route->delete('/company/#company_id/delete', "Company@delete"); 
    break;
    default:    
    break;
}
