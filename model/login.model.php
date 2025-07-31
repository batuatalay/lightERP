<?php



class LoginModel extends BaseORM {

    protected static $table = 'users';

    public static function createUserLogin($userID, $token) {
        $userLogin = self::from('user_login')->where('user_id', '=', $userID)->get();
        if (!$userLogin) {
            $loginID = UUIDHelper::generate();
            $user = [
                'login_id' => $loginID,
                'user_id' => $userID,
                'token' => $token,
                'status' => 'active',
                'login_date' => DateHelper::get()
            ];
            try {
                $userLogin = self::from('user_login')->insert($user)->execute();
                return $userLogin;
            } catch (PDOException $e) {
                ExceptionHandler::convertPDOException($e);
            } catch (Exception $e) {
                throw new DatabaseException('Failed to login user: ' . $e->getMessage(), 'USER_LOGIN_FAILED');
            }
        } else {
            throw new DatabaseException('User already signIn', 'USER_LOGIN_FAILED');

        }
    }

    public static function getUserLogin($userID) {
        try {
            if (!$userID) {
                throw new ValidationException('User ID is required', 'USER_ID_REQUIRED');
            } else {
                $userLogin = self::from('user_login')->where('user_id', '=', $userID)->first();
                return $userLogin;
            }
        } catch (PDOException $e) {
            ExceptionHandler::convertPDOException($e);
        } catch (Exception $e) {
            throw new DatabaseException('User Login Not found: ' . $e->getMessage(), 'USER_LOGIN_FAILED');
        }
    }
    public static function userSignOut($userLogin) {
        try {
            self::from('user_login')->where('user_id', '=', $userLogin['id'])->delete();
        } catch (PDOException $e) {
            ExceptionHandler::convertPDOException($e);
        } catch (Exception $e) {
            throw new DatabaseException('User logout failed: ' . $e->getMessage(), 'USER_LOGOUT_FAILED');
        }
    }
}