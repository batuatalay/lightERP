<?php
class UserModel extends BaseORM {
    protected static $table = 'users';
    protected static $primaryKey = 'user_id';
    protected static $fillable = ['name', 'username', 'email', 'password'];

    public static function create($params) {
        $now = date('Y-m-d h:i:s');
        $userID = UUIDHelper::generate();
        $user = [
            'user_id' => $userID,
            'name' => $params['name'],
            'username' => $params['username'],
            'email' => $params['email'],
            'password' => PasswordHelper::hash($params['password']),
            'created_at' => DateHelper::get(),
            'updated_at' => DateHelper::get()
        ];
        try {
            self::from(static::$table)->insert($user)->execute();
            return $userID;
        } catch (PDOException $e) {
            ExceptionHandler::convertPDOException($e);
        } catch (Exception $e) {
            throw new DatabaseException('Failed to create user: ' . $e->getMessage(), 'USER_CREATE_FAILED');
        }
    }

    public static function getUser($username) {
        if (!$username) {
            throw new ValidationException('Username is required', 'USERNAME_REQUIRED');
        }
        
        try {
            $user = self::select()->from(static::$table)->where('username', '=', $username)->first();
            if (!$user) {
                throw new NotFoundException('User not found', 'USER_NOT_FOUND');
            }
            return $user;
        } catch (PDOException $e) {
            ExceptionHandler::convertPDOException($e);
        } catch (BaseException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new DatabaseException('Failed to retrieve user: ' . $e->getMessage(), 'USER_FETCH_FAILED');
        }
    }

    public static function findUser($username) {
        if (!$username) {
            return null;
        }
        try {
            $user = self::select()->from(static::$table)->where('username', '=', $username)->first();
            return $user; // null or user object
        } catch (PDOException $e) {
            ExceptionHandler::convertPDOException($e);
        } catch (Exception $e) {
            throw new DatabaseException('Failed to retrieve user: ' . $e->getMessage(), 'USER_FETCH_FAILED');
        }
    }

    public static function getUserOrganization($userID) {
        if (!$userID) {
            throw new ValidationException('User ID is required', 'USER_ID_REQUIRED');
        }
        
        try {
            $userOrganization = self::select()->from('organization_user')->where('user_id','=',$userID)->first();
            if (!$userOrganization) {
                throw new NotFoundException('User organization relationship not found', 'USER_ORG_NOT_FOUND');
            }
            return $userOrganization;
        } catch (PDOException $e) {
            ExceptionHandler::convertPDOException($e);
        } catch (BaseException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new DatabaseException('Failed to retrieve user organization: ' . $e->getMessage(), 'USER_ORG_FETCH_FAILED');
        }
    }
}