<?php
class InventoryService  extends BaseService  {
    public function __construct() {
        parent::__construct();
    }

    public function getInventory() {
        $user = SessionHelper::getUserData();
        $organizationID = $user["organization_id"];
        $products = ProductModel::getProducts($organizationID);
        var_dump($products);exit;
    }

    public function getWarehouses() {
        $organizationID = SessionHelper::getUserData('organization_id');
        $warehouses = WarehouseModel::getWarehouses($organizationID);
        var_dump($warehouses);exit;
    }
}