<?php

class SessionHelper {
    public static function createUserSession($userData) {
        if(!isset($_SESSION['user'])) {
            $_SESSION['user'] = [
                'id' => $userData['id'] ?? null,
                'name' => $userData['name'] ?? '',
                'username' => $userData['username'] ?? '',
                'user_role' => $userData['user_role'] ?? 'user',
                'organization_id' => $userData['organization_id'] ?? null,
                'permissions' => $userData['permissions'] ?? []
            ];
        } else {
            echo 'You are already logged in<br>';
        }
    }

    public static function getUserData($key = null) {
        if(!$key) {
            return $_SESSION['user'] ?? null;
        } else {
            return $_SESSION['user'][$key] ?? null;
        }
    }

    public static function hasPermission($permission) {
        $userData = self::getUserData();
        if (!$userData) return false;
        return in_array($permission, $userData['permissions']);
    }

    public static function getUserRole() {
        $userData = self::getUserData();
        return $userData['user_role'] ?? null;
    }

    public static function isLoggedIn() {
        return isset($_SESSION['user']);
    }

    public static function isAdmin() {
        $userData = self::getUserData();
        if (!$userData) return false;
        return $userData['user_role'] === 'admin';
    }

    // NEW: Multi-tenant methods
    public static function getCurrentOrganization(): ?string {
        $userData = self::getUserData();
        return $userData['organization_id'] ?? null;
    }

    public static function belongsToOrganization($orgId): bool {
        return self::getCurrentOrganization() === $orgId;
    }

    public static function isSuperAdmin(): bool {
        $userData = self::getUserData();
        if (!$userData) return false;
        return $userData['user_role'] === 'superadmin';
    }

    public static function setOrganizationContext($organizationId): void {
        if (isset($_SESSION['user'])) {
            $_SESSION['user']['organization_id'] = $organizationId;
        }
    }

    public static function getOrganizationContext(): ?string {
        return $_SESSION['current_organization_context'] ?? self::getCurrentOrganization();
    }

    public static function destroySession() {
        unset($_SESSION['user']);
        unset($_SESSION['current_organization_context']);
        session_destroy();
        echo "Session destroyed<br>";
    }

    public static function changeUser() {
        $_SESSION['user'] = [
            'id' => 2,
            'name' => 'Regular User',
            'username' => 'user',
            'user_role' => 'user',
            'organization_id' => 'org-123', // NEW: Test organization
            'permissions' => ['read']
        ];
        echo "User changed to regular user with organization: org-123<br>";
    }
}