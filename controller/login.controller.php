<?php 
require_once BASE . "/helper/session.helper.php";
require_once BASE . "/helper/password.helper.php";
require_once BASE . "/helper/return.helper.php";
require_once BASE . "/model/user.model.php";
require_once BASE . "/model/permission.model.php";
require_once BASE . "/exception/exception.handler.php";

#[Prefix('login')]
class Login extends SimpleController{

    public static function loginPage() {
    }

    public static function signIn($params) {
        try {
            if (!isset($params['username']) || !isset($params['password'])) {
                throw new ValidationException('Username and password are required', 'LOGIN_CREDENTIALS_REQUIRED');
            }
            
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
                echo json_encode(['success' => true, 'message' => 'Successfully logged in']);
            } else {
                throw new AuthenticationException('Invalid credentials', 'INVALID_CREDENTIALS');
            }
        } catch (ValidationException $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => $e->getMessage(), 'error_code' => $e->getErrorCode()]);
        } catch (NotFoundException $e) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Invalid credentials', 'error_code' => 'INVALID_CREDENTIALS']);
        } catch (AuthenticationException $e) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => $e->getMessage(), 'error_code' => $e->getErrorCode()]);
        } catch (DatabaseException $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage(), 'error_code' => $e->getErrorCode()]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Internal server error', 'error_code' => 'INTERNAL_ERROR']);
            error_log("Login error: " . $e->getMessage());
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