<?php
require_once BASE . "/router.php";
require_once BASE . "/controller/organization.controller.php";

class Router extends BaseRouter {
}

$route = new Router();
switch ($_SERVER["REQUEST_METHOD"]) {
	case 'GET':
		break;
	case 'POST':
		break;
	case 'PUT':
		$route->put('/organization/create', "Organization@create");
		break;
    case 'DELETE':
		$route->delete('/organization/#organization_id/delete', "Organization@deleteOrganization");
		break;
}