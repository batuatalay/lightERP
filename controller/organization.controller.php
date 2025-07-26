<?php 
require_once BASE . "/helper/return.helper.php";
require_once BASE . "/exception/exception.handler.php";
require_once BASE . "/service/organization.service.php";

require_once BASE . "/middleware/organization/organization.middleware.php";

class Organization extends SimpleController {
    
    #[LoginAttribute]
    #[OrganizationAdminAttribute]
    public static function deleteOrganization($params) {
        try {
            $organizationService = new OrganizationService();
            $organizationService->deleteOrganization($params);
            ReturnHelper::success('Organization successfully deleted');
        } catch (ValidationException $e) {
            ExceptionHandler::handleException($e);
        } catch (NotFoundException $e) {
            ExceptionHandler::handleException($e);
        } catch (DatabaseException $e) {
            ExceptionHandler::handleException($e);
        } catch (Exception $e) {
            ExceptionHandler::handleException($e);
        }
    }

    #[LoginAttribute]
    public static function create($params) {
        try {
            $organizationService = new OrganizationService();
            $organizationID = $organizationService->createOrganization($params);
            ReturnHelper::success('Organization successfully created', ['organization_id' => $organizationID]);
        } catch (ValidationException $e) {
            ExceptionHandler::handleException($e);
        } catch (ConflictException $e) {
            ExceptionHandler::handleException($e);
        } catch (DatabaseException $e) {
            ExceptionHandler::handleException($e);
        } catch (Exception $e) {
            ExceptionHandler::handleException($e);
        }
    }
}