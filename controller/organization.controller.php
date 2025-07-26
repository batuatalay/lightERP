<?php 
require_once BASE . "/model/organization.model.php";
require_once BASE . "/helper/date.helper.php";
require_once BASE . "/helper/return.helper.php";
require_once BASE . "/exception/exception.handler.php";
require_once BASE . "/helper/session.helper.php";

require_once BASE . "/middleware/organization/organization.middleware.php";

class Organization extends SimpleController {
    
    #[LoginAttribute]
    #[OrganizationAdminAttribute]
    public static function deleteOrganization($params) {
        try {
            if (OrganizationModel::deleteByUUID($params)) {
                ReturnHelper::success('Organization successfully deleted');
            } else {
                ReturnHelper::fail("Organization Delete failed");
            }
        } catch (ValidationException $e) {
            ReturnHelper::error($e->getMessage(), $e->getErrorCode(), 400);
        } catch (NotFoundException $e) {
            ReturnHelper::error($e->getMessage(), $e->getErrorCode(), 404);
        } catch (DatabaseException $e) {
            ReturnHelper::error($e->getMessage(), $e->getErrorCode(), 500);
        } catch (Exception $e) {
            ReturnHelper::error('Internal server error', 'INTERNAL_ERROR', 500);
            error_log("Organization deletion error: " . $e->getMessage());
        }
    }

    #[LoginAttribute]
    public static function create($params) {
        try {
            DateHelper::now();
            $organizationID = OrganizationModel::create($params);
            OrganizationModel::createOrganizationUser(SessionHelper::getUserData('id'), $organizationID, 'admin');
            OrganizationModel::createProperties($organizationID, $params['properties']);
            ReturnHelper::success('Organization successfully created', ['organization_id' => $organizationID]);
        } catch (ValidationException $e) {
            ReturnHelper::error($e->getMessage(), $e->getErrorCode(), 400);
        } catch (ConflictException $e) {
            ReturnHelper::error($e->getMessage(), $e->getErrorCode(), 409);
        } catch (DatabaseException $e) {
            ReturnHelper::error($e->getMessage(), $e->getErrorCode(), 500);
        } catch (Exception $e) {
            ReturnHelper::error('Internal server error', 'INTERNAL_ERROR', 500);
            error_log("Organization creation error: " . $e->getMessage());
        }
    }
}