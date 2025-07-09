<?php
require_once BASE . "/helper/password.helper.php";
require_once BASE . "/helper/uuid.helper.php";
require_once BASE . '/orm/BaseORM.php';

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
        } catch (Exception $e) {
            echo 'User cannot created';
        }
    }

    public static function getUser($username) {
        if($username) {
            $user = self::select()->from(static::$table)->where('username', '=', $username)->first();
            return $user;
        } else {
            echo 'there is no user';
        }
    }

    public static function getUserOrganization($userID) {
        if($userID) {
            $userOrganization = self::select()->from('organization_user')->where('user_id','=',$userID)->first();
            return $userOrganization;
        }
    }
}