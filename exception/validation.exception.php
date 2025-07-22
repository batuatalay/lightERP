<?php

require_once 'base.exception.php';

class ValidationException extends BaseException {
    protected $httpStatusCode = 400;
    
    public function __construct($message = "Validation failed", $errorCode = 'VALIDATION_ERROR') {
        parent::__construct($message, $errorCode);
    }
}