<?php
require_once BASE . "/helper/session.helper.php";
require_once BASE . "/exception/exception.handler.php";

#[Attribute]
class ApiAuthAttribute {
    public function __construct(
        public readonly array $methods = ['jwt', 'bearer'], // Authentication methods
        public readonly bool $optional = false // Allow optional authentication
    ) {}

    public function handle($next, $params) {
        $authHeader = $this->getAuthorizationHeader();
        
        if (!$authHeader) {
            return $this->handleMissingAuth();
        }

        $user = null;

        // Try JWT authentication
        if (in_array('jwt', $this->methods) && str_starts_with($authHeader, 'Bearer ')) {
            $token = substr($authHeader, 7);
            $user = $this->authenticateJWT($token);
        }

        // Try API key authentication
        if (!$user && in_array('bearer', $this->methods) && str_starts_with($authHeader, 'Bearer ')) {
            $token = substr($authHeader, 7);
            $user = $this->authenticateApiKey($token);
        }

        if (!$user) {
            return $this->handleInvalidAuth();
        }

        // Create temporary session for API user
        $this->createApiSession($user);

        echo "ApiAuthMiddleware: User authenticated via API<br>";
        return $next($params);
    }

    /**
     * Get Authorization header
     */
    private function getAuthorizationHeader(): ?string {
        // Check Authorization header
        if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            return $_SERVER['HTTP_AUTHORIZATION'];
        }
        
        // Check alternative header
        if (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
            return $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
        }

        // Check for API key in query params (less secure)
        if (isset($_GET['api_key'])) {
            return 'Bearer ' . $_GET['api_key'];
        }

        return null;
    }

    /**
     * Authenticate JWT token
     */
    private function authenticateJWT(string $token): ?array {
        try {
            // Basic JWT validation (in production, use proper JWT library)
            $parts = explode('.', $token);
            
            if (count($parts) !== 3) {
                return null;
            }

            $header = json_decode(base64_decode($parts[0]), true);
            $payload = json_decode(base64_decode($parts[1]), true);
            $signature = $parts[2];

            // Verify signature (simplified - use proper JWT library in production)
            $expectedSignature = base64_encode(hash_hmac('sha256', $parts[0] . '.' . $parts[1], $this->getJwtSecret(), true));
            
            if (!hash_equals(rtrim($expectedSignature, '='), rtrim($signature, '='))) {
                return null;
            }

            // Check expiration
            if (isset($payload['exp']) && $payload['exp'] < time()) {
                return null;
            }

            // Return user data from JWT payload
            return [
                'id' => $payload['user_id'] ?? null,
                'name' => $payload['name'] ?? '',
                'username' => $payload['username'] ?? '',
                'user_role' => $payload['role'] ?? 'user',
                'organization_id' => $payload['organization_id'] ?? null,
                'permissions' => $payload['permissions'] ?? [],
                'auth_method' => 'jwt'
            ];

        } catch (Exception $e) {
            error_log("JWT Authentication error: " . $e->getMessage());
            throw new AuthenticationException('Invalid JWT token format', 'JWT_INVALID');
        }
    }

    /**
     * Authenticate API key
     */
    private function authenticateApiKey(string $apiKey): ?array {
        // This would typically query the database for API key
        // For demo purposes, return test data for specific key
        
        if ($apiKey === 'demo-api-key-123') {
            return [
                'id' => 100,
                'name' => 'API User',
                'username' => 'api_user',
                'user_role' => 'api',
                'organization_id' => 'org-123',
                'permissions' => ['read', 'write'],
                'auth_method' => 'api_key'
            ];
        }

        // In production, query database:
        /*
        $apiKeyModel = ApiKey::where('key', $apiKey)
                            ->where('status', 'active')
                            ->where('expires_at', '>', date('Y-m-d H:i:s'))
                            ->first();
        
        if ($apiKeyModel) {
            return $apiKeyModel->user->toArray();
        }
        */

        return null;
    }

    /**
     * Create temporary session for API authentication
     */
    private function createApiSession(array $userData): void {
        // Don't overwrite existing web session
        if (!SessionHelper::isLoggedIn()) {
            $_SESSION['api_user'] = $userData;
            $_SESSION['user'] = $userData; // Make it compatible with existing middleware
        }
    }

    /**
     * Handle missing authentication
     */
    private function handleMissingAuth() {
        if ($this->optional) {
            return; // Continue without authentication
        }

        throw new AuthenticationException('Please provide valid authentication credentials', 'AUTH_REQUIRED');
    }

    /**
     * Handle invalid authentication
     */
    private function handleInvalidAuth() {
        throw new AuthenticationException('The provided authentication credentials are invalid', 'AUTH_INVALID');
    }

    /**
     * Get JWT secret key
     */
    private function getJwtSecret(): string {
        // In production, store this in environment variables
        return defined('JWT_SECRET') ? JWT_SECRET : 'your-super-secret-jwt-key-change-this-in-production';
    }
}

/**
 * Utility class for generating JWT tokens
 */
class JWTHelper {
    public static function generateToken(array $userData, int $expirationHours = 24): string {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        
        $payload = json_encode([
            'user_id' => $userData['id'],
            'username' => $userData['username'],
            'name' => $userData['name'],
            'role' => $userData['user_role'],
            'organization_id' => $userData['organization_id'],
            'permissions' => $userData['permissions'],
            'iat' => time(),
            'exp' => time() + ($expirationHours * 3600)
        ]);

        $base64Header = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64Payload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));

        $signature = hash_hmac('sha256', $base64Header . "." . $base64Payload, self::getJwtSecret(), true);
        $base64Signature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

        return $base64Header . "." . $base64Payload . "." . $base64Signature;
    }

    private static function getJwtSecret(): string {
        return defined('JWT_SECRET') ? JWT_SECRET : 'your-super-secret-jwt-key-change-this-in-production';
    }
}