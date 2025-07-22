<?php

require_once 'base.exception.php';
require_once 'validation.exception.php';
require_once 'database.exception.php';
require_once 'authentication.exception.php';
require_once 'authorization.exception.php';
require_once 'notfound.exception.php';
require_once 'conflict.exception.php';

class ExceptionHandler {
    
    public static function handleException($exception) {
        if ($exception instanceof BaseException) {
            self::handleCustomException($exception);
        } else {
            self::handleGenericException($exception);
        }
    }
    
    private static function handleCustomException(BaseException $exception) {
        http_response_code($exception->getHttpStatusCode());
        
        $response = $exception->toArray();
        
        error_log(sprintf(
            "[%s] %s - Error Code: %s, HTTP Status: %d, File: %s, Line: %d",
            date('Y-m-d H:i:s'),
            $exception->getMessage(),
            $exception->getErrorCode(),
            $exception->getHttpStatusCode(),
            $exception->getFile(),
            $exception->getLine()
        ));
        
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
    
    private static function handleGenericException(Exception $exception) {
        http_response_code(500);
        
        $response = [
            'error' => true,
            'error_code' => 'INTERNAL_ERROR',
            'message' => 'An internal server error occurred',
            'http_status' => 500
        ];
        
        error_log(sprintf(
            "[%s] Generic Exception: %s, File: %s, Line: %d",
            date('Y-m-d H:i:s'),
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine()
        ));
        
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
    
    public static function convertPDOException(PDOException $e) {
        $errorInfo = $e->errorInfo ?? [];
        $sqlState = $errorInfo[0] ?? '';
        $errorCode = $errorInfo[1] ?? 0;
        
        switch ($sqlState) {
            case '23000': // Integrity constraint violation
                if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                    throw new ConflictException("Resource already exists", 'DUPLICATE_ENTRY');
                } else {
                    throw new ValidationException("Data integrity violation", 'INTEGRITY_VIOLATION');
                }
                break;
                
            case '42S02': // Table doesn't exist
                throw new DatabaseException("Database table not found", 'TABLE_NOT_FOUND');
                break;
                
            case '42000': // Syntax error
                throw new DatabaseException("Database query syntax error", 'SYNTAX_ERROR');
                break;
                
            case '08S01': // Communication link failure
            case '2002': // Connection refused
            case '2006': // MySQL server has gone away
                throw new DatabaseException("Database connection failed", 'CONNECTION_ERROR');
                break;
                
            default:
                throw new DatabaseException("Database operation failed: " . $e->getMessage(), 'DATABASE_ERROR');
        }
    }
    
    public static function setGlobalHandler() {
        set_exception_handler([self::class, 'handleException']);
        
        set_error_handler(function($severity, $message, $file, $line) {
            if (!(error_reporting() & $severity)) {
                return false;
            }
            
            $exception = new DatabaseException("PHP Error: $message in $file:$line", 'PHP_ERROR');
            self::handleException($exception);
        });
    }
}