<?php
// Load environment variables
function loadEnv($path) {
    if (!file_exists($path)) {
        throw new Exception(".env file not found: $path");
    }
    
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '#') === 0) continue; // Skip comments
        if (strpos($line, '=') === false) continue; // Skip invalid lines
        
        list($key, $value) = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        
        // Remove quotes if present
        if (preg_match('/^"(.*)"$/', $value, $matches)) {
            $value = $matches[1];
        }
        
        $_ENV[$key] = $value;
        putenv("$key=$value");
        define($key, $value);
    }
}

// Load .env file
loadEnv(__DIR__ . '/.env');