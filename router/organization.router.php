<?php
require_once BASE . "/router.php";
require_once BASE . "/middleware/common.middleware.php";
require_once BASE . "/controller/organization.controller.php";

class Router extends BaseRouter {
}

$route = new Router();

// Web Routes (HTML responses)
$route->get('/organization', "Organization@dashboard");
$route->get('/organization/dashboard', "Organization@dashboard");
$route->get('/organization/list', "Organization@index");
$route->get('/organization/#organization_id', "Organization@show");
$route->post('/organization/create', "Organization@create");
$route->post('/organization/#organization_id/update', "Organization@update");
$route->post('/organization/#organization_id/delete', "Organization@delete");

// Property Management Routes (Web)
$route->get('/organization/#organization_id/property/#key', "Organization@getProperty");
$route->post('/organization/#organization_id/property/set', "Organization@setProperty");