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
    public static function getParents($organizationID) {
        if (empty($organizationID)) {
            throw new ValidationException('Organization ID is required', 'ORGANIZATION_ID_REQUIRED');
        }
        try {
            $parents = self::select(['category_id', 'code', 'name', 'status'])
                ->from(static::$table)
                ->where('organization_id', '=', $organizationID)
                ->where('status', '=', 'active')
                ->whereNull('parent_id')
                ->get();
            return $parents;
            if (empty($parents)) {
                throw new NotFoundException('No active categories found for this organization', 'CATEGORIES_NOT_FOUND');
            }
            return $parents;
        } catch (PDOException $e) {
            ExceptionHandler::convertPDOException($e);
        } catch (NotFoundException $e) {
            throw $e; // Re-throw NotFoundException as-is
        } catch (Exception $e) {
            throw new DatabaseException('Failed to fetch categories: ' . $e->getMessage(), 'CATEGORY_FETCH_ERROR');
        }
    }
    public static function getSubCategories($parentID) {
        if (empty($parentID)) {
            throw new ValidationException('Category ID is required', 'ORGANIZATION_ID_REQUIRED');
        }
        try {
            $subCategories = self::select(['category_id', 'code', 'name', 'status'])
                ->from(static::$table)
                ->where('parent_id', '=', $parentID)
                ->where('status', '=', 'active')
                ->get();
            if(empty($subCategories)) {
                throw new NotFoundException('No active sub categories found for this category', 'CATEGORIES_NOT_FOUND');
            }
            return $subCategories;

        }  catch (PDOException $e) {
            ExceptionHandler::convertPDOException($e);
        } catch (NotFoundException $e) {
            throw $e; // Re-throw NotFoundException as-is
        } catch (Exception $e) {
            throw new DatabaseException('Failed to fetch categories: ' . $e->getMessage(), 'CATEGORY_FETCH_ERROR');
        }

    }
    public static function getCategories($organization_id) {
        // Validate required parameters
        if (empty($organization_id)) {
            throw new ValidationException('Organization ID is required', 'ORGANIZATION_ID_REQUIRED');
        }
        
        try {
            $categories = self::select(['category_id', 'parent_id', 'code', 'name', 'status'])
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