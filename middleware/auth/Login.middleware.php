<?php
require_once BASE . "/helper/session.helper.php";

#[Attribute]
class LoginAttribute {
    public function __construct() {}

    public function handle($next, $params) {
        if (!SessionHelper::isLoggedIn()) {
            echo 'please log in';
            exit;
        }
        return $next($params);
    }
} 