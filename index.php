<?php
error_reporting(E_ALL);
ini_set("display_errors", 0);
spl_autoload_register( function($className) {
    if($className == "SimpleController") {
        $fullPath = BASE . "controller/simple.controller.php";
    } else {
        $extension = ".controller.php";
        $fullPath = BASE . "controller/" . strtolower($className) . $extension;
    }
    require_once $fullPath;
});

session_start();

require_once "init.php";
require_once "model/mysql.php";
require_once "orm/BaseORM.php";

$dirName = dirname($_SERVER['SCRIPT_NAME']);
$baseName = basename($_SERVER['SCRIPT_NAME']);
$constantArr = explode("/", ltrim($_SERVER["REQUEST_URI"], "/"));
if(empty($constantArr[0])) {
	header('Location:main');
} else {
	define('URI', $constantArr);
	if(file_exists(BASE . '/router/' . URI[0] . ".router.php")) {
		require_once BASE . '/router/' . URI[0] . ".router.php";
	}
}

?>
