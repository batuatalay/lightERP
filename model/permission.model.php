<?php
require_once BASE . '/orm/BaseORM.php';
require_once BASE . '/exception/exception.handler.php';
require_once BASE . '/helper/uuid.helper.php';
require_once BASE . '/helper/date.helper.php';

class PermissionModel extends BaseORM {
    protected static $table = 'permissions';
    protected static $primaryKey = 'permission_id';

    public static function create($params) {
        // Validate required parameters
        $requiredFields = ['name', 'description'];
        foreach ($requiredFields as $field) {
            if (!isset($params[$field]) || empty(trim($params[$field]))) {
                throw new ValidationException("Field '{$field}' is required", 'FIELD_REQUIRED');
            }
        }
        
        $permissionID = UUIDHelper::generate();
        $permission = [
            'permission_id' => $permissionID,
            'name' => $params['name'],
            'description' => $params['description'],
            'created_at' => DateHelper::get(),
            'updated_at' => DateHelper::get()
        ];
        
        try {
            self::from(static::$table)->insert($permission)->execute();
            return $permissionID;
        } catch (PDOException $e) {
            ExceptionHandler::convertPDOException($e);
        } catch (Exception $e) {
            throw new DatabaseException("Failed to create permission: " . $e->getMessage(), 'PERMISSION_CREATE_ERROR');
        }
    }

    public static function createOrganizationUserPermission($userID, $organizationID, $permissions) {
        // Validate required parameters
        if (empty($userID)) {
            throw new ValidationException('User ID is required', 'USER_ID_REQUIRED');
        }
        if (empty($organizationID)) {
            throw new ValidationException('Organization ID is required', 'ORGANIZATION_ID_REQUIRED');
        }
        if (empty($permissions) || !is_array($permissions)) {
            throw new ValidationException('Permissions array is required', 'PERMISSIONS_REQUIRED');
        }
        
        try {
            foreach ($permissions as $permission) {
                // Validate permission structure
                if (!isset($permission['id']) || !isset($permission['level'])) {
                    throw new ValidationException('Permission must have id and level', 'INVALID_PERMISSION_STRUCTURE');
                }
                
                $organizationPermission = [
                    'organization_id' => $organizationID,
                    'user_id' => $userID,
                    'permission_id' => $permission['id'],
                    'level' => $permission['level'],
                    'updated_at' => DateHelper::get()
                ];
                self::from('organization_user_permissions')->insert($organizationPermission)->execute();
            }
        } catch (PDOException $e) {
            ExceptionHandler::convertPDOException($e);
        } catch (Exception $e) {
            throw new DatabaseException('Failed to create user permissions: ' . $e->getMessage(), 'PERMISSION_CREATE_ERROR');
        }
    }

    public static function getUserPermissions($organizationID, $userID) {
        // Validate required parameters
        if (empty($organizationID)) {
            throw new ValidationException('Organization ID is required', 'ORGANIZATION_ID_REQUIRED');
        }
        if (empty($userID)) {
            throw new ValidationException('User ID is required', 'USER_ID_REQUIRED');
        }
        
        try {
            $permissions = self::select()
                ->from('organization_user_permissions')
                ->where('organization_id', '=', $organizationID)
                ->where('user_id', '=', $userID)
                ->get();
                
            if (empty($permissions)) {
                throw new NotFoundException('No permissions found for this user in the organization', 'PERMISSIONS_NOT_FOUND');
            }
                
            return $permissions;
        } catch (PDOException $e) {
            ExceptionHandler::convertPDOException($e);
        } catch (NotFoundException $e) {
            throw $e; // Re-throw NotFoundException as-is
        } catch (Exception $e) {
            throw new DatabaseException('Failed to fetch user permissions: ' . $e->getMessage(), 'PERMISSION_FETCH_ERROR');
        }
    }
}