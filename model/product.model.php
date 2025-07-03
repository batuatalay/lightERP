<?php
class ProductModel extends BaseORM {
    protected static $table = 'products';
    protected static $primaryKey = 'product_id';

    public function __construct($arr = [])
    {
        parent::__construct($arr);
        foreach ($arr as $key => $value) {
            $this->$key = $value;
        }
    }
    public static function getProducts($organization_id, $category_id) {
        if (!$organization_id && !$category_id) {
            return null;
        }
        
        try {
            // OrganizationModel'deki pattern'i kullan
            $products = self::select()
                ->from(static::$table)
                ->where('organization_id', '=', $organization_id)
                ->where('category_id', '=', $category_id)
                ->get();
            return $products; // var_dump ve exit kaldır
        } catch (Exception $e) {
            error_log("Error fetching products: " . $e->getMessage());
            return null;
        }
    }
}