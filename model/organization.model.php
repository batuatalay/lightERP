<?php

class OrganizationModel extends BaseORM {
    protected static $table = 'organizations';
    protected static $primaryKey = 'organization_id';
    protected static $fillable = [
        'organization_name',
        'organization_slug', 
        'status'
    ];

    public function getOrganization($user_id) {
        if(!$user_id) {
            return null;
        } else {
            $usersOrganization = self::select()->from('organization_user')->where('user_id','=',$user_id)->first();
            if ($usersOrganization) {
                $organization = self::select()->from(static::$table)->where("organization_id",'=',$usersOrganization['organization_id'])->get();
                return $organization;
            }
        }
    }
    public function isOrganizationAdmin($organization_id, $user_id) {
        $result = self::select()
        ->from('organization_user')
        ->where("organization_id",'=',$organization_id)
        ->where("user_id",'=',$user_id)
        ->first();
        if($result['role'] == 'admin') {
            return true;
        }
    }

    public static function getAllActive() {
        return static::where('status', 'active');
    }
}