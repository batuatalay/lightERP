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
        $instance = new static();
        $db = $instance->db;
        try {
            $db->beginTransaction();
            $result = $callback();
            $db->commit();
            return $result;
        } catch (Exception $e) {
            $db->rollback();
            throw $e;
        }
    }
}