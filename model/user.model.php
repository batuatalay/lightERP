<?php
// model/user.model.php (Raw SQL ile düzeltilmiş)
require_once BASE . '/orm/BaseORM.php';

class UserModel extends BaseORM {
    protected static $table = 'users';
    protected static $primaryKey = 'user_id';
    protected static $fillable = ['name', 'username', 'email', 'password'];
    
public static function getAllUsers($organizationId = null) {
    if ($organizationId) {
        return static::raw("
            SELECT 
                u.user_id,
                u.name,
                u.username, 
                u.email,
                u.created_at,
                ou.role as organization_role,
                GROUP_CONCAT(
                    CONCAT(p.type, ':', oup.level) 
                    ORDER BY p.type 
                    SEPARATOR ','
                ) as permissions
            FROM users u
            LEFT JOIN organization_user ou ON u.user_id = ou.user_id AND ou.organization_id = ?
            LEFT JOIN organization_user_permissions oup ON u.user_id = oup.user_id AND oup.organization_id = ?
            LEFT JOIN permissions p ON oup.permission_id = p.permission_id
            GROUP BY u.user_id, u.name, u.username, u.email, u.created_at, ou.role
            ORDER BY u.name, u.username
        ", [$organizationId, $organizationId]);
    } else {
        return static::raw("
            SELECT 
                u.user_id,
                u.name,
                u.username, 
                u.email,
                u.created_at,
                o.organization_name as organization,
                GROUP_CONCAT(
                    DISTINCT CONCAT(p.type, ':', oup.level)
                    ORDER BY o.organization_name, p.type 
                    SEPARATOR '; '
                ) as all_permissions,
                COUNT(DISTINCT ou.organization_id) as organization_count
            FROM users u
            LEFT JOIN organization_user ou ON u.user_id = ou.user_id
            LEFT JOIN organizations o ON ou.organization_id = o.organization_id
            LEFT JOIN organization_user_permissions oup ON u.user_id = oup.user_id AND ou.organization_id = oup.organization_id
            LEFT JOIN permissions p ON oup.permission_id = p.permission_id
            GROUP BY u.user_id, u.name, u.username, u.email, u.created_at, o.organization_name
            ORDER BY o.organization_name, u.name, u.username
        ");
    }
}
    public static function getUsersByOrganizationRole($organizationId, $role) {
        return static::raw("
            SELECT u.*, ou.role as organization_role
            FROM users u
            JOIN organization_user ou ON u.user_id = ou.user_id
            WHERE ou.organization_id = ? AND ou.role = ?
            ORDER BY u.name
        ", [$organizationId, $role]);
    }
    
    public static function getOrganizationAdmins($organizationId) {
        return static::getUsersByOrganizationRole($organizationId, 'admin');
    }
    
    public static function getOrganizationOwners($organizationId) {
        return static::getUsersByOrganizationRole($organizationId, 'owner');
    }
    
    // Kullanıcı arama - organization ile
    public static function searchUsersInOrganization($query, $organizationId) {
        return static::raw("
            SELECT u.*, ou.role as organization_role
            FROM users u
            LEFT JOIN organization_user ou ON u.user_id = ou.user_id AND ou.organization_id = ?
            WHERE (u.name LIKE ? OR u.username LIKE ? OR u.email LIKE ?)
            ORDER BY u.name
        ", [$organizationId, "%{$query}%", "%{$query}%", "%{$query}%"]);
    }
    
    public function getOrganizations() {
        return static::raw("
            SELECT o.*, ou.role
            FROM organizations o
            JOIN organization_user ou ON o.organization_id = ou.organization_id
            WHERE ou.user_id = ?
            ORDER BY o.organization_name
        ", [$this->user_id]);
    }
    
    public function getPermissionsInOrganization($organizationId) {
        return static::raw("
            SELECT p.type, oup.level
            FROM organization_user_permissions oup
            JOIN permissions p ON oup.permission_id = p.permission_id
            WHERE oup.user_id = ? AND oup.organization_id = ?
            ORDER BY p.type
        ", [$this->user_id, $organizationId]);
    }
    
    public static function getOrganizationUserStats($organizationId) {
        return static::raw("
            SELECT ou.role, COUNT(*) as count
            FROM organization_user ou
            JOIN users u ON ou.user_id = u.user_id
            WHERE ou.organization_id = ?
            GROUP BY ou.role
        ", [$organizationId]);
    }
    
    public function getRoleInOrganization($organizationId) {
        $result = static::raw("
            SELECT ou.role
            FROM organization_user ou
            WHERE ou.user_id = ? AND ou.organization_id = ?
        ", [$this->user_id, $organizationId]);
        
        return !empty($result) ? $result[0]['role'] : null;
    }
    
    public function hasPermissionInOrganization($organizationId, $permissionType, $requiredLevel = 1) {
        $result = static::raw("
            SELECT oup.level
            FROM organization_user_permissions oup
            JOIN permissions p ON oup.permission_id = p.permission_id
            WHERE oup.user_id = ? AND oup.organization_id = ? AND p.type = ?
        ", [$this->user_id, $organizationId, $permissionType]);
        
        if (empty($result)) return false;
        
        return intval($result[0]['level']) >= $requiredLevel;
    }
    
    public function isAdminInOrganization($organizationId) {
        $role = $this->getRoleInOrganization($organizationId);
        return in_array($role, ['admin', 'owner']);
    }
    
    public function isOwnerInOrganization($organizationId) {
        $role = $this->getRoleInOrganization($organizationId);
        return $role === 'owner';
    }
    
    // Basic metodlar
    public static function getUserById($id) {
        return static::find($id);
    }
    
    public static function getUserByUsername($username) {
        $users = static::where('username', $username);
        return !empty($users) ? $users[0] : null;
    }
    
    public static function getUserByEmail($email) {
        $users = static::where('email', $email);
        return !empty($users) ? $users[0] : null;
    }
    
    // Search users (genel)
    public static function searchUsers($query) {
        return static::raw("
            SELECT * FROM users 
            WHERE name LIKE ? OR username LIKE ? OR email LIKE ? 
            ORDER BY name
        ", ["%{$query}%", "%{$query}%", "%{$query}%"]);
    }
    
    public static function getRecentUsers($days = 30) {
        return static::raw("
            SELECT * FROM users 
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
            ORDER BY created_at DESC
        ", [$days]);
    }
    
    // Password handling
    public function setPassword($password) {
        $this->password = password_hash($password, PASSWORD_DEFAULT);
        return $this;
    }
    
    public function verifyPassword($password) {
        return password_verify($password, $this->password);
    }
}