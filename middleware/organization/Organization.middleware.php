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
        if (!SessionHelper::isLoggedIn()) {
            header("Location: /login");
            exit;
        }
        
        // Sonra admin kontrolü
        if (!SessionHelper::isAdmin()) {
            http_response_code(403);
            header("Location: /main");
            exit("Access denied. Admin privileges required.");
        }
        
        $userId = 1;
        $organizationId = 1;
        if (!$this->isOrganizationAdmin($organizationId, $userId)) {
            http_response_code(403);
            header("Location: /main");
            exit("The user who logged in, is not organization Admin.");
        }
        
        echo "OrganizationAdmin executed!<br>";
        return $next($params);
    }
    
    public function isOrganizationAdmin($organization_id, $user_id) {
        return self::$model->isOrganizationAdmin($organization_id, $user_id);
    }
}