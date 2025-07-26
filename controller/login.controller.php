<?php 
require_once BASE . "/helper/session.helper.php";
require_once BASE . "/helper/return.helper.php";
require_once BASE . "/exception/exception.handler.php";

require_once BASE . "/service/login.service.php";

#[Prefix('login')]
class Login extends SimpleController{

    public static function loginPage() {
    }

    public static function signIn($params) {
        try {
            $loginService = new LoginService();
            if(!$loginService->isLoggedIn()) {
                $loginService->validateLoginCredentials($params);
                $loginService->authenticate();
                ReturnHelper::success('Successfully logged in');
            }
        } catch (AuthenticationException $e) {
            ExceptionHandler::handleException($e);
        } catch (ValidationException $e) {
            ExceptionHandler::handleException($e);
        } catch (DatabaseException $e) {
            ExceptionHandler::handleException($e);
        } catch (Exception $e) {
            ExceptionHandler::handleException($e);
        }
    }

    public function signOut(){
        try {
            SessionHelper::destroySession();
            ReturnHelper::success('You are successfully signout');
        } catch (Exception $e) {
            ExceptionHandler::handleException($e);
        }
    }

    public static function loginCheck() {
        return SessionHelper::isLoggedIn();
    }

    public static function changeUser() {
        try {
            SessionHelper::changeUser();
            header("Location: /main");
            exit;
        } catch (Exception $e) {
            ExceptionHandler::handleException($e);
        }
    }
}