<?php
class ProductModel extends BaseORM {
    protected static $table = 'inventory_products';
    protected static $primaryKey = 'product_id';

    public function __construct($arr = [])
    {
        parent::__construct($arr);
        foreach ($arr as $key => $value) {
            $this->$key = $value;
        }
    }
    public static function getProducts($organization_id, $category_id = null) {
        if (empty($organization_id)) {
            throw new ValidationException('Organization ID is required', 'ORGANIZATION_ID_REQUIRED');
        }
        try {
            if(empty($category_id)) {
                $products = self::select()
                    ->from(static::$table)
                    ->where('organization_id', '=', $organization_id)
                    ->get();
            } else {
                $products = self::select()
                    ->from(static::$table)
                    ->where('organization_id', '=', $organization_id)
                    ->where('category_id', '=', $category_id)
                    ->get();
            }
            if (empty($products)) {
                throw new NotFoundException('No products found for this organization and category', 'PRODUCTS_NOT_FOUND');
            }
            return $products;
        }  catch (PDOException $e) {
            ExceptionHandler::convertPDOException($e);
        } catch (NotFoundException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new DatabaseException('Failed to fetch products: ' . $e->getMessage(), 'PRODUCT_FETCH_ERROR');
        }
    }
}