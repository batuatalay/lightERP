<?php
/**
 * Password Helper Class
 * Uygulama içinde kullanım için
 */

class PasswordHelper {
    private static $hashKey = null;

    public static function init() {
        if (self::$hashKey === null) {
            self::$hashKey = $_ENV['HASH_KEY'] ?? 'default_fallback_key';
        }
    }
    
    /**
     * @param string $password
     * @param bool $useCustomSalt .env'den salt kullan
     * @return string
     */
    public static function hash($password, $useCustomSalt = true) {
        self::init();
        if ($useCustomSalt) {
            return hash('sha256', $password . self::$hashKey);
        } else {
            return password_hash($password, PASSWORD_DEFAULT);
        }
    }
    
    /**
     * @param string $password
     * @param string $hash
     * @param bool $useCustomSalt
     * @return bool
     */
    public static function verify($password, $hash, $useCustomSalt = false) {
        self::init();
        
        if ($useCustomSalt) {
            return hash('sha256', $password . self::$hashKey) === $hash;
        } else {
            return password_verify($password, $hash);
        }
    }
    
    /**
     * @param string $password
     * @param string $hash
     * @return bool
     */
    public static function autoVerify($password, $hash) {
        // bcrypt hash mi kontrol et
        if (strpos($hash, '$2y$') === 0 || strpos($hash, '$2a$') === 0 || strpos($hash, '$2x$') === 0) {
            return self::verify($password, $hash, false);
        } 
        // SHA256 hash (64 karakter) mi kontrol et
        else if (strlen($hash) === 64 && ctype_xdigit($hash)) {
            return self::verify($password, $hash, true);
        }
        
        return false;
    }
    
    /**
     * @param int $length
     * @return string
     */
    public static function generateSalt($length = 32) {
        return bin2hex(random_bytes($length / 2));
    }
}