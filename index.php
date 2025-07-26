<?php
error_reporting(E_ALL);
ini_set("display_errors", 0);
spl_autoload_register( function($className) {
    if($className == "SimpleController") {
        $fullPath = BASE . "controller/simple.controller.php";
    } else {
        if(strpos($className, "Controller") !== false) {
            $extension = ".controller.php";
            $fullPath = BASE . "controller/" . strtolower($className) . $extension;
        } else if(strpos($className, "Model") !== false) {
            $modelName = strtolower(str_replace("Model", "", $className));
            $extension = ".model.php";
            $fullPath = BASE . "model/" . $modelName . $extension;
        } else if(strpos($className, "Service") !== false) {
            $modelName = strtolower(str_replace("Service", "", $className));
            $extension = ".service.php";
            $fullPath = BASE . "service/" . $modelName . $extension;
        } else if(strpos($className, "Helper") !== false) {
            $modelName = strtolower(str_replace("Helper", "", $className));
            $extension = ".helper.php";
            $fullPath = BASE . "helper/" . $modelName . $extension;
        }
        else {
        }
    }
    require_once $fullPath;
});

session_start();

require_once "init.php";
require_once "model/mysql.php";
require_once "orm/BaseORM.php";
require_once "exception/main.exception.php";


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
