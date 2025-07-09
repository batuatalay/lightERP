<?php
require_once BASE . '/orm/BaseORM.php';

class PermissionModel extends BaseORM {
    protected static $table = 'permissions';
    protected static $primaryKey = 'permission_id';

    public static function create($params) {
        $now = date('Y-m-d h:i:s');
        $userID = UUIDHelper::generate();
        $user = [
            'user_id' => $userID,
            'name' => $params['name'],
            'username' => $params['username'],
            'email' => $params['email'],
            'password' => PasswordHelper::hash($params['password']),
            'created_at' => DateHelper::get(),
            'updated_at' => DateHelper::get()
        ];
        try {
            self::from(static::$table)->insert($user)->execute();
            return $userID;
        } catch (Exception $e) {
            return $e;
        }
    }

    public static function createOrganizationUserPermission($userID, $organizationID, $permissions) {
        try {
            foreach ($permissions as $permission) {
                $organizationPermission = [
                    'organization_id' => $organizationID,
                    'user_id' => $userID,
                    'permission_id' => $permission['id'],
                    'level' => $permission['level'],
                    'updated_at' => DateHelper::get()
                ];
                self::from('organization_user_permissions')->insert($organizationPermission)->execute();
            }
        } catch (Exception $e) {
            echo 'User permissions cannot created';exit;
        }
    }

    public static function getUserPermissions ($organizationID, $userID) {
        $permissions = self::select()
        ->from('organization_user_permissions')
        ->where('organization_id', '=', $organizationID)
        ->where('user_id', '=', $userID)
        ->get();
        return $permissions;
    }
}