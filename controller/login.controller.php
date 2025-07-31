<?php
#[Prefix('login')]
class Login extends SimpleController{

    public function loginPage() {
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
            LoginModel::userSignOut(SessionHelper::getUserData());
            SessionHelper::destroySession();
            ReturnHelper::success('You are successfully signout');
        } catch (Exception $e) {
            ExceptionHandler::handleException($e);
        }
    }

    public static function loginCheck() {
        return SessionHelper::isLoggedIn();
    }

}