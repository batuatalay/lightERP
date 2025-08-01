<?php 
require_once BASE . "/middleware/common.middleware.php";
require_once BASE . "/middleware/organization/Organization.middleware.php";

class Inventory extends SimpleController{

	#[LoginAttribute]
    public static function getWarehouses($organizationID){
        $service = new InventoryService();
        $service->getWarehouses();
        var_dump($warehouses);exit;
    }

	public static function getInventory () {
        $service = new InventoryService();
        $service->getInventory();
		echo PHP_EOL . 'getting all inventories';
	
	}

	public static function testFunction2() {
		echo PHP_EOL . 'test Function 2';
		
	}
}