<?php
require_once BASE . "/middleware/common.middleware.php";
require_once BASE . "/model/company.model.php";

#[Attribute]
class CompanyPermissionAttribute {
    protected static $permissionID;
    protected static $model;
    
    public function __construct() {
        self::$permissionID = '750e8400-e29b-41d4-a716-446655440006';
        if (self::$model === null) {
            self::$model = new CompanyModel();
        }
    }
    public function handle($next, $params) {
        if(SessionHelper::isAdmin()) {
            return $next($params);
        }
        
        $userPermissions = SessionHelper::getUserData('permissions');
        foreach ($userPermissions as $permission) {
            if($permission['permission_id'] == static::$permissionID) {
                if($permission['level'] == 1) {
                    ReturnHelper::fail('You dont have add & edit permission');
                    exit;
                } else {
                    break;
                }
            }
        }
        return $next($params);
    }
}

#[Attribute]
class CompanyDeletePermissionAttribute {
    protected static $permissionID;
    protected static $model;
    
    public function __construct() {
        self::$permissionID = '750e8400-e29b-41d4-a716-446655440006';
        if (self::$model === null) {
            self::$model = new CompanyModel();
        }
    }
    public function handle($next, $params) {
        if(SessionHelper::isAdmin()) {
            return $next($params);
        }
        
        $userPermissions = SessionHelper::getUserData('permissions');
        foreach ($userPermissions as $permission) {
            if($permission['permission_id'] == static::$permissionID) {
                if($permission['level'] <= 2) {
                    ReturnHelper::fail('You dont have delete permission');
                    exit;
                } else {
                    break;
                }
            }
        }
        return $next($params);
    }
}