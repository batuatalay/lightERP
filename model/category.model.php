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
        // Validate required parameters
        if (empty($organization_id)) {
            throw new ValidationException('Organization ID is required', 'ORGANIZATION_ID_REQUIRED');
        }
        
        try {
            $categories = self::select()
                ->from(static::$table)
                ->where('organization_id', '=', $organization_id)
                ->where('status', '=', 'active')
                ->get();
                
            if (empty($categories)) {
                throw new NotFoundException('No active categories found for this organization', 'CATEGORIES_NOT_FOUND');
            }
            
            return $categories;
        } catch (PDOException $e) {
            ExceptionHandler::convertPDOException($e);
        } catch (NotFoundException $e) {
            throw $e; // Re-throw NotFoundException as-is
        } catch (Exception $e) {
            throw new DatabaseException('Failed to fetch categories: ' . $e->getMessage(), 'CATEGORY_FETCH_ERROR');
        }
    }
}