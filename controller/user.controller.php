<?php 
require_once BASE . "/helper/session.helper.php";
require_once BASE . "/helper/date.helper.php";
require_once BASE . "/exception/exception.handler.php";

require_once BASE . "/model/user.model.php";
require_once BASE . "/model/organization.model.php";
require_once BASE . "/model/permission.model.php";


#[Prefix('login')]
class User extends SimpleController{

    public static function loginPage() {
    }

    public static function createUser($params) {
        try {
            DateHelper::now();
            $userID = UserModel::create($params);
            if($userID) {
                OrganizationModel::createOrganizationUser($userID,$params['organization_id'],$params['role']);
                PermissionModel::createOrganizationUserPermission($userID,$params['organization_id'],$params['permissions']);
                echo json_encode([
                    'success' => true,
                    'user_id' => $userID,
                    'message' => 'User created successfully'
                ]);
            }
        } catch (ValidationException $e) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage(),
                'error_code' => $e->getErrorCode()
            ]);
        } catch (ConflictException $e) {
            http_response_code(409);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage(),
                'error_code' => $e->getErrorCode()
            ]);
        } catch (DatabaseException $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage(),
                'error_code' => $e->getErrorCode()
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Internal server error',
                'error_code' => 'INTERNAL_ERROR'
            ]);
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

    public static function changeUser() {
        SessionHelper::changeUser();
        header("Location: /main");
        exit;
    }
}