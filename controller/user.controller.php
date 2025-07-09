<?php 
require_once BASE . "/helper/session.helper.php";
require_once BASE . "/helper/date.helper.php";

require_once BASE . "/model/user.model.php";
require_once BASE . "/model/organization.model.php";
require_once BASE . "/model/permission.model.php";


#[Prefix('login')]
class User extends SimpleController{

    public static function loginPage() {
    }

    public static function createUser($params) {
        DateHelper::now();
        $userID = UserModel::create($params);
        if($userID) {
            OrganizationModel::createOrganizationUser($userID,$params['organization_id'],$params['role']);
            PermissionModel::createOrganizationUserPermission($userID,$params['organization_id'],$params['permissions']);
            echo $userID;
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

    public static function changeUser() {
        SessionHelper::changeUser();
        header("Location: /main");
        exit;
    }
}