<?php
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

    public function isLoggedIn(){
        if(isset($_SESSION['user'])) {
            throw new AuthenticationException('You are already logged in');
        } else {
            return false;
        }
    }
}