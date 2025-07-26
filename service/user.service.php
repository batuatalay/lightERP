<?php
class UserService extends BaseService
{
    const requiredFields = [
        'name',
        'username',
        'email',
        'password',
        'organization_id',
    ];

    public function __construct()
    {
        parent::__construct();
    }
    private static function checkBusinessRule($userData) {
        $existingUser = UserModel::findUser($userData['username']);
        if($existingUser) {
            throw new ConflictException("Username already exists", "USERNAME_EXISTS");
        }
        $existingUser = UserModel::from('users')->where('email', $userData['email'])->first();
        if($existingUser) {
            throw new ConflictException("Email already exists", "EMAIL_EXISTS");
        }
        $organizationExist = OrganizationModel::getOrganizationByUUID($userData['organization_id']);
        if(!$organizationExist) {
            throw new ConflictException("Organization not found", "ORGANIZATION_NOT_FOUND");
        }
    }
    public static function createUser(array $userData) {
        self::validateRequired(self::requiredFields, $userData);
        self::checkBusinessRule($userData);
        return self::executeInTransaction(function() use ($userData) {
            DateHelper::now();
            $userID = UserModel::create($userData);
            if($userID) {
                OrganizationModel::createOrganizationUser(
                    $userID,
                    $userData['organization_id'],
                    $userData['role']
                );
                if (!empty($userData['permissions'])) {
                    PermissionModel::createOrganizationUserPermission(
                        $userID,
                        $userData['organization_id'],
                        $userData['permissions']
                    );
                }
                return $userID;

            } else {
                throw new DatabaseException("User cannot be created");
            }
        });
    }
}
?>

