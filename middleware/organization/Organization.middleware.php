<?php
require_once BASE . "/middleware/common.middleware.php";
require_once BASE . "/model/organization.model.php";
#[Attribute]
class OrganizationAdminAttribute {
    protected static $model;
    
    public function __construct() {
        if (self::$model === null) {
            self::$model = new OrganizationModel();
        }
    }
    
    public function handle($next, $params) {        
        // Sonra admin kontrolü
        if (!SessionHelper::isAdmin()) {
            ReturnHelper::fail("Access denied. Admin privileges required.");
            exit;
        }
        
        $userId = SessionHelper::getUserData('id');
        $organizationId = $params;
        if (!$this->isOrganizationAdmin($organizationId, $userId)) {
            ReturnHelper::fail("The user who logged in, is not organization Admin.");
            exit;
        }
        return $next($params);
    }
    
    public function isOrganizationAdmin($organization_id, $user_id) {
        return self::$model->isOrganizationAdmin($organization_id, $user_id);
    }
}