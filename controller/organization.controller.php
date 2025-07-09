<?php 
require_once BASE . "/model/organization.model.php";
require_once BASE . "/helper/date.helper.php";
require_once BASE . "/helper/return.helper.php";

require_once BASE . "/middleware/organization/organization.middleware.php";

class Organization extends SimpleController {
    
    #[LoginAttribute]
    #[OrganizationAdminAttribute]
    public static function deleteOrganization($params) {
        if (OrganizationModel::deleteByUUID($params)) {
            ReturnHelper::success('Organization successfully deleted');
        } else {
            ReturnHelper::fail("Organization Delete failed");
        }
    }

    #[LoginAttribute]
    public static function create($params) {
        DateHelper::now();
        try {
            $organizationID = OrganizationModel::create($params);
            OrganizationModel::createOrganizationUser(SessionHelper::getUserData('id'), $organizationID, 'admin');
            OrganizationModel::createProperties($organizationID, $params['properties']);
        } catch(Exception $e) {
            ReturnHelper::fail("Organization create failed");
        }
        ReturnHelper::success('Organization successfully created');
    }
}