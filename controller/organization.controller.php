<?php 
require_once BASE . "/model/organization.model.php";

class Organization extends SimpleController {

    public static function deleteOrganization($params) {
        if (OrganizationModel::deleteByUUID($params)) {
            echo $params . ' successfully deleted';
        } else {
            echo "fail bitch";
        }
    }
}