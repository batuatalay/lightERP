<?php

require_once 'base.exception.php';

class DatabaseException extends BaseException {
    protected $httpStatusCode = 500;
    
    public function __construct($message = "Database operation failed", $errorCode = 'DATABASE_ERROR') {
        parent::__construct($message, $errorCode);
    }
}