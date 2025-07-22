<?php

require_once 'base.exception.php';

class ConflictException extends BaseException {
    protected $httpStatusCode = 409;
    
    public function __construct($message = "Resource already exists", $errorCode = 'CONFLICT') {
        parent::__construct($message, $errorCode);
    }
}