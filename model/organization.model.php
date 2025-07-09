<?php
require_once BASE . "/helper/uuid.helper.php";
require_once BASE . "/helper/slug.helper.php";

class OrganizationModel extends BaseORM {
    protected static $table = 'organizations';
    protected static $primaryKey = 'organization_id';

    public function getOrganization($user_id) {
        if(!$user_id) {
            return null;
        } else {
            $usersOrganization = self::select()->from('organization_user')->where('user_id','=',$user_id)->first();
            if ($usersOrganization) {
                $organization = self::select()->from(static::$table)->where("organization_id",'=',$usersOrganization['organization_id'])->get();
                return $organization;
            }
        }
    }
    public function isOrganizationAdmin($organization_id, $user_id) {
        $result = self::select()
        ->from('organization_user')
        ->where("organization_id",'=',$organization_id)
        ->where("user_id",'=',$user_id)
        ->first();
        if($result['role'] == 'admin') {
            return true;
        }
    }

    public static function getAllActive() {
        return static::where('status', 'active');
    }

    public static function deleteByUUID($organizationID) {
        try {
            self::from('organizations')->where('organization_id','=',$organizationID)->delete();
            return true;
        } catch (Exception $e) {
            error_log("Error fetching categories: " . $e->getMessage());
            return null;
        }
    }

    public static function createOrganizationUser($userID, $organizationID, $role) {
        $organizationUser = [
            'organization_id' => $organizationID,
            'user_id' => $userID,
            'role' => $role,
            'updated_at' => DateHelper::get()
        ];
        try {
            self::from('organization_user')->insert($organizationUser)->execute();
        } catch (Exception $e) {
            echo 'User could not be assigned to Organization';exit;
        }
    }

    public static function create($params) {
        if($params) {
            try {
                $organizationID = UUIDHelper::generate();
                $organization = [
                    'organization_id' => $organizationID,
                    'organization_name' => $params['organization_name'],
                    'organization_slug' => SlugHelper::generate($params['organization_name']),
                    'status' => $params['status'],
                    'created_at' => DateHelper::get(),
                    'updated_at' => DateHelper::get()
                ];
                self::from(static::$table)->insert($organization)->execute();
                return $organizationID;
            } catch (Exception $e) {
                echo 'organization cannot created';
            }

        }
    }

    public static function createProperties($organizationID, $properties) {
        try {
            foreach ($properties as $property) {
                $organizationProperties = [
                    'org_property_id' => UUIDHelper::generate(),
                    'organization_id' => $organizationID,
                    'property_key' => $property['key'],
                    'property_value' => $property['value'],
                    'property_type' => $property['type'],
                    'created_at' => DateHelper::get(),
                    'updated_at' => DateHelper::get()
                ];
                self::from('organization_properties')->insert($organizationProperties)->execute();
            }
        } catch (Exception $e) {
            echo 'Organization Properties cannot created';exit;
        }
    }
}