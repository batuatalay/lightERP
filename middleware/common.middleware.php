<?php
require_once BASE . "/helper/session.helper.php";

#[Attribute]
class AdminAttribute {
    public function __construct() {}

    public function handle($next, $params) {
        if (!SessionHelper::isLoggedIn()) {
            header("Location: /login");
            exit;
        }
        if (!SessionHelper::isAdmin()) {
            http_response_code(403);
            ReturnHelper::fail("Access denied. Admin privileges required.");
            exit;
        }
        return $next($params);
    }
}

#[Attribute]
class LoginAttribute {
    public function __construct() {}

    public function handle($next, $params) {
        if (!SessionHelper::isLoggedIn()) {
            ReturnHelper::fail("Please Log in");
            exit;
        }
        return $next($params);
    }
}

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS)]
class AuthAttribute {
    public function __construct(
        public readonly string $role = 'user'
    ) {}

    public function handle($next, $params) {
        if (SessionHelper::getUserRole() == $this->role) {
            echo "Admin user detected!<br>";
        } else {
            echo "Regular user detected!<br>";
        }
        
        return $next($params);
    }
}