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
    }
}

// Load .env file
loadEnv(__DIR__ . '/.env');

// Define constants from environment variables
define("DEVELOPMENT", $_ENV['DEVELOPMENT'] === 'true');
define("ENV", $_ENV['ENV']);
define("BASE", $_ENV['BASE']);
define("PANEL", $_ENV['PANEL']);

define("DBHOST", $_ENV['DBHOST']);
define("DBNAME", $_ENV['DBNAME']);
define("DBUSERNAME", $_ENV['DBUSERNAME']);
define("DBPASSWORD", $_ENV['DBPASSWORD']);

define("EMAIL", $_ENV['EMAIL']);
define("EMAILPASSWORD", $_ENV['EMAILPASSWORD']);
define("SMTPHOST", $_ENV['SMTPHOST']);