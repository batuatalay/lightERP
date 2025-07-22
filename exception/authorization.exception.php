<?php

require_once 'base.exception.php';

class AuthorizationException extends BaseException {
    protected $httpStatusCode = 403;
    
    public function __construct($message = "Access denied", $errorCode = 'ACCESS_DENIED') {
        parent::__construct($message, $errorCode);
    }
}