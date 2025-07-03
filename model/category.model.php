<?php
class CategoryModel extends BaseORM {
    protected static $table = 'categories';
    protected static $primaryKey = 'category_id';

    public function __construct($arr = [])
    {
        parent::__construct($arr);
        foreach ($arr as $key => $value) {
            $this->$key = $value;
        }
    }
    public static function getCategories($organization_id) {
        if(!$organization_id) {
            return null;
        }
        try {
            $categories = self::select()
            ->from(static::$table)
            ->where('organization_id','=',$organization_id)
            ->where('status','=','active')
            ->get();
            return $categories;
        } catch (Exception $e) {
            error_log("Error fetching categories: " . $e->getMessage());
            return null;
        }
    }
}