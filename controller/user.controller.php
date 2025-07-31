<?php
#[Prefix('login')]
class User extends SimpleController{

    public static function loginPage() {
    }

    public static function createUser($params) {
        try {
            $userID = userService::createUser($params);
            if($userID) {
                ReturnHelper::success('User created successfully');
            }
        } catch (ValidationException $e) {
            ReturnHelper::error($e->getMessage(), $e->getErrorCode(), 400);
        } catch (ConflictException $e) {
            ReturnHelper::error($e->getMessage(), $e->getErrorCode(), 409);
        } catch (DatabaseException $e) {
            ReturnHelper::error($e->getMessage(), $e->getErrorCode(), 500);
        } catch (Exception $e) {
            ReturnHelper::error('Internal server error', 'INTERNAL_ERROR', 500);
            error_log("User creation error: " . $e->getMessage());
        }
    }

    public function signOut(){
        SessionHelper::destroySession();
        header("Location: /login");
        exit;
    }

    public static function loginCheck() {
        return SessionHelper::isLoggedIn();
    }
}