<?php
abstract class BaseService {
    protected $db;

    public function __construct() {
        $this->db = new Mysql();
        $this->db->connect();
    }

    protected static function validateRequired($requiredFields, $userData) {
        foreach ($requiredFields as $field) {
            if (empty($userData[$field])) {
                throw new ValidationException("$field is required");
            }
        }
    }
    protected static function executeInTransaction($callback) {
        // Static method'ta instance oluştur
        $instance = new static();
        $db = $instance->db;
        
        try {
            // 1. Transaction başlat
            $db->beginTransaction();

            // 2. İşlemleri çalıştır
            $result = $callback();

            // 3. Hepsi başarılıysa commit et
            $db->commit();

            return $result;

        } catch (Exception $e) {
            // 4. Hata varsa rollback yap
            $db->rollback();

            // 5. Exception'ı tekrar fırlat
            throw $e;
        }
    }
}