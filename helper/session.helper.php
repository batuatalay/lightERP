<?php

class SessionHelper {
    public static function createUserSession($token) {
        if(!isset($_SESSION['token'])) {
            $_SESSION['token'] = $token;
        }
    }

    public static function getUserData($key = null) {
        if(isset($_SESSION['token'])) {
            $user = JWTHelper::validateToken($_SESSION['token']);
            if(!$key) {
                return $user ?? null;
            } else {
                return $user[$key] ?? null;
            }
        }
    }

    public static function getUserRole() {
        $userData = self::getUserData();
        return $userData['user_role'] ?? null;
    }

    public static function isLoggedIn() {
        $user = self::getUserData();
        return isset($user);
    }

    public static function isAdmin() {
        $userData = self::getUserData();
        if (!$userData) return false;
        return $userData['user_role'] === 'admin';
    }



    public static function destroySession() {
        unset($_SESSION['token']);
        session_destroy();
    }
}