<?php 
require_once BASE . "/helper/session.helper.php";
require_once BASE . "/helper/password.helper.php";
require_once BASE . "/model/user.model.php";
require_once BASE . "/model/permission.model.php";

#[Prefix('login')]
class Login extends SimpleController{

    public static function loginPage() {
    }

    public static function signIn($params) {
        $username = $params['username'];
        $password = PasswordHelper::hash($params['password']);
        $user = UserModel::getUser($username);
        if($user['password'] == $password) {
            $userOrganization = UserModel::getUserOrganization($user['user_id']);
            $userPermissions = PermissionModel::getUserPermissions($userOrganization['organization_id'], $user['user_id']);
            $sessionUser = [
                'id' => $user['user_id'],
                'name' => $user['name'],
                'username' => $user['username'],
                'organization_id' => $userOrganization['organization_id'],
                'user_role' => $userOrganization['role'],
                'permissions' => $userPermissions
            ];
            SessionHelper::createUserSession($sessionUser);
            echo 'successfully logged in';
        }
    }

    public function signOut(){
        SessionHelper::destroySession();
        ReturnHelper::success('You are successfully signout');
    }

    public static function loginCheck() {
        return SessionHelper::isLoggedIn();
    }

    public static function changeUser() {
        SessionHelper::changeUser();
        header("Location: /main");
        exit;
    }
}