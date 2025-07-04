<?php
require_once BASE . "/router.php";
require_once BASE . "/middleware/common.middleware.php";
require_once BASE . "/controller/organization.controller.php";

class Router extends BaseRouter {
}

$route = new Router();

$route->delete('/organization/#organization_id/delete', "Organization@deleteOrganization");