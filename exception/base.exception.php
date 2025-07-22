<?php

abstract class BaseException extends Exception {
    protected $errorCode;
    protected $httpStatusCode = 500;
    
    public function __construct($message = "", $errorCode = 0, Exception $previous = null) {
        $this->errorCode = $errorCode;
        parent::__construct($message, 0, $previous);
    }
    
    public function getErrorCode() {
        return $this->errorCode;
    }
    
    public function getHttpStatusCode() {
        return $this->httpStatusCode;
    }
    
    public function toArray() {
        return [
            'error' => true,
            'error_code' => $this->errorCode,
            'message' => $this->getMessage(),
            'http_status' => $this->httpStatusCode
        ];
    }
}