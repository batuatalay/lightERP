<?php

require_once 'base.exception.php';

class AuthenticationException extends BaseException {
    protected $httpStatusCode = 401;
    
    public function __construct($message = "Authentication failed", $errorCode = 'AUTH_ERROR') {
        parent::__construct($message, $errorCode);
    }
}