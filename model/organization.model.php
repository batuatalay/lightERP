<?php
require_once BASE . "/helper/uuid.helper.php";
require_once BASE . "/helper/slug.helper.php";
require_once BASE . "/helper/date.helper.php";
require_once BASE . '/orm/BaseORM.php';
require_once BASE . "/exception/exception.handler.php";

class OrganizationModel extends BaseORM {
    protected static $table = 'organizations';
    protected static $primaryKey = 'organization_id';

    public function getOrganization($user_id) {
        if(!$user_id) {
            throw new ValidationException("User ID is required", 'MISSING_USER_ID');
        }
        
        try {
            $usersOrganization = self::select()->from('organization_user')->where('user_id','=',$user_id)->first();
            if (!$usersOrganization) {
                throw new NotFoundException("No organization found for user", 'USER_ORGANIZATION_NOT_FOUND');
            }
            
            $organization = self::select()->from(static::$table)->where("organization_id",'=',$usersOrganization['organization_id'])->get();
            if (!$organization) {
                throw new NotFoundException("Organization not found", 'ORGANIZATION_NOT_FOUND');
            }
            
            return $organization;
        } catch (PDOException $e) {
            ExceptionHandler::convertPDOException($e);
        }
    }

    public static function getOrganizationByUUID($uuid) {
        if(!$uuid) {
            throw new ValidationException("UUID is required", 'MISSING_UUID');
        }
        $organization = self::from("organizations")->where("organization_id",'=',$uuid)->first();
        if(!$organization) {
            throw new NotFoundException("Organization not found", 'ORGANIZATION_NOT_FOUND');
        }
        return $organization;

    }
    public function isOrganizationAdmin($organization_id, $user_id) {
        if (!$organization_id || !$user_id) {
            throw new ValidationException("Organization ID and User ID are required", 'MISSING_REQUIRED_PARAMETERS');
        }
        
        try {
            $result = self::select()
            ->from('organization_user')
            ->where("organization_id",'=',$organization_id)
            ->where("user_id",'=',$user_id)
            ->first();
            
            if (!$result) {
                throw new NotFoundException("User not found in organization", 'USER_NOT_IN_ORGANIZATION');
            }
            
            return $result['role'] === 'admin';
        } catch (PDOException $e) {
            ExceptionHandler::convertPDOException($e);
        }
    }

    public static function getAllActive() {
        try {
            return static::where('status', 'active');
        } catch (PDOException $e) {
            ExceptionHandler::convertPDOException($e);
        }
    }

    public static function deleteByUUID($organizationID) {
        if (!$organizationID) {
            throw new ValidationException("Organization ID is required", 'MISSING_ORGANIZATION_ID');
        }
        
        try {
            // Check if organization exists before deletion
            $existing = self::select()->from('organizations')->where('organization_id','=',$organizationID)->first();
            if (!$existing) {
                throw new NotFoundException("Organization not found", 'ORGANIZATION_NOT_FOUND');
            }
            
            self::from('organizations')->where('organization_id','=',$organizationID)->delete();
            return true;
        } catch (PDOException $e) {
            ExceptionHandler::convertPDOException($e);
        }
    }

    public static function createOrganizationUser($userID, $organizationID, $role) {
        if (!$userID || !$organizationID || !$role) {
            throw new ValidationException("User ID, Organization ID, and role are required", 'MISSING_REQUIRED_PARAMETERS');
        }
        
        // Validate role
        $validRoles = ['owner','admin', 'member', 'viewer', 'user'];
        if (!in_array($role, $validRoles)) {
            throw new ValidationException("Invalid role specified", 'INVALID_ROLE');
        }
        
        $organizationUser = [
            'organization_id' => $organizationID,
            'user_id' => $userID,
            'role' => $role,
            'updated_at' => DateHelper::get()
        ];
        
        try {
            self::from('organization_user')->insert($organizationUser)->execute();
            return true;
        } catch (PDOException $e) {
            ExceptionHandler::convertPDOException($e);
        }
    }

    public static function create($params) {
        if (!$params) {
            throw new ValidationException("Parameters are required", 'MISSING_PARAMETERS');
        }
        
        // Validate required fields
        if (!isset($params['organization_name']) || empty(trim($params['organization_name']))) {
            throw new ValidationException("Organization name is required", 'MISSING_ORGANIZATION_NAME');
        }
        
        if (!isset($params['status']) || empty(trim($params['status']))) {
            throw new ValidationException("Organization status is required", 'MISSING_STATUS');
        }
        
        // Validate status
        $validStatuses = ['active', 'inactive', 'pending'];
        if (!in_array($params['status'], $validStatuses)) {
            throw new ValidationException("Invalid status specified", 'INVALID_STATUS');
        }
        
        try {
            $organizationID = UUIDHelper::generate();
            $organization = [
                'organization_id' => $organizationID,
                'organization_name' => trim($params['organization_name']),
                'organization_slug' => SlugHelper::generate($params['organization_name']),
                'status' => $params['status'],
                'created_at' => DateHelper::get(),
                'updated_at' => DateHelper::get()
            ];
            
            self::from(static::$table)->insert($organization)->execute();
            return $organizationID;
        } catch (PDOException $e) {
            ExceptionHandler::convertPDOException($e);
        }
    }

    public static function createProperties($organizationID, $properties) {
        if (!$organizationID) {
            throw new ValidationException("Organization ID is required", 'MISSING_ORGANIZATION_ID');
        }
        
        if (!$properties || !is_array($properties) || empty($properties)) {
            throw new ValidationException("Properties array is required and cannot be empty", 'MISSING_PROPERTIES');
        }
        
        try {
            foreach ($properties as $property) {
                // Validate each property
                if (!isset($property['key']) || empty(trim($property['key']))) {
                    throw new ValidationException("Property key is required", 'MISSING_PROPERTY_KEY');
                }
                
                if (!isset($property['value'])) {
                    throw new ValidationException("Property value is required", 'MISSING_PROPERTY_VALUE');
                }
                
                if (!isset($property['type']) || empty(trim($property['type']))) {
                    throw new ValidationException("Property type is required", 'MISSING_PROPERTY_TYPE');
                }
                
                // Validate property type
                $validTypes = ['string', 'integer', 'boolean', 'json', 'text'];
                if (!in_array($property['type'], $validTypes)) {
                    throw new ValidationException("Invalid property type specified", 'INVALID_PROPERTY_TYPE');
                }
                
                $organizationProperties = [
                    'org_property_id' => UUIDHelper::generate(),
                    'organization_id' => $organizationID,
                    'property_key' => trim($property['key']),
                    'property_value' => $property['value'],
                    'property_type' => $property['type'],
                    'created_at' => DateHelper::get(),
                    'updated_at' => DateHelper::get()
                ];
                
                self::from('organization_properties')->insert($organizationProperties)->execute();
            }
            
            return true;
        } catch (PDOException $e) {
            ExceptionHandler::convertPDOException($e);
        }
    }
}