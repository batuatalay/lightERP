<?php

require_once 'base.exception.php';

class NotFoundException extends BaseException {
    protected $httpStatusCode = 404;
    
    public function __construct($message = "Resource not found", $errorCode = 'NOT_FOUND') {
        parent::__construct($message, $errorCode);
    }
}