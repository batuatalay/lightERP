<?php
require_once BASE . "/helper/session.helper.php";
require_once BASE . "/exception/main.exception.php";

#[Attribute]
class LoginAttribute {
    public function __construct() {}

    public function handle($next, $params) {
        if (!SessionHelper::isLoggedIn()) {
            throw new AuthenticationException('Authentication required. Please log in.', 'LOGIN_REQUIRED');
        }
        return $next($params);
    }
} 