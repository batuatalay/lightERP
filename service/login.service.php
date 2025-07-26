<?php
require_once BASE . "service/base.service.php";
require_once BASE . "exception/authentication.exception.php";

require_once BASE . "model/user.model.php";
require_once BASE . "model/permission.model.php";

require_once BASE . "helper/password.helper.php";
require_once BASE . "helper/uuid.helper.php";
require_once BASE . "helper/date.helper.php";
require_once BASE . "helper/session.helper.php";

class LoginService extends BaseService {
    private $username, $password, $user, $sessionUser;
    const requiredFields = [
        'username',
        'password',
    ];
    public function __construct()
    {
        parent::__construct();
    }
    public function validateLoginCredentials($params) {
        self::validateRequired(self::requiredFields, $params);
        $this->username = $params['username'];
        $this->password = $params['password'];
    }
    public function authenticate() {
        $this->user = UserModel::getUser($this->username);
        if (empty($this->user)) {
            throw new AuthenticationException('Invalid username', 'INVALID_CREDENTIALS');
        } else {
            if (!isset($this->user['password']) || empty($this->user['password'])) {
                throw new AuthenticationException('User password not found', 'INVALID_CREDENTIALS');
            }
            if (!PasswordHelper::autoVerify($this->password, $this->user['password'])) {
                throw new AuthenticationException('Invalid password', 'INVALID_CREDENTIALS');
            }
        }
        $this->getUserData();
        SessionHelper::createUserSession($this->sessionUser);

    }

    public function getUserData() {
        $userOrganization = UserModel::getUserOrganization($this->user['user_id']);
        $userPermissions = PermissionModel::getUserPermissions($userOrganization['organization_id'], $this->user['user_id']);
        $this->sessionUser = [
            'id' => $this->user['user_id'],
            'name' => $this->user['name'],
            'username' => $this->user['username'],
            'organization_id' => $userOrganization['organization_id'],
            'user_role' => $userOrganization['role'],
            'permissions' => $userPermissions
        ];
    }
}