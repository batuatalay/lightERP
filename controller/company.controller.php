<?php 
require_once BASE . "/middleware/common.middleware.php";
require_once BASE . "/model/company.model.php";
require_once BASE . "/helper/date.helper.php";
require_once BASE . "/helper/return.helper.php";
require_once BASE . "/exception/exception.handler.php";


require_once BASE . "/middleware/company/company.middleware.php";

class Company extends SimpleController{

	public static function getAll() {
		try {
			$companies = CompanyModel::getAllCompaniesWithContacts();
			ReturnHelper::successWithData($companies);
		} catch (DatabaseException $e) {
			ReturnHelper::error($e->getMessage(), $e->getErrorCode(), 500);
		} catch (Exception $e) {
			ReturnHelper::error('Internal server error', 'INTERNAL_ERROR', 500);
			error_log("Company getAll error: " . $e->getMessage());
		}
	}
	#[LoginAttribute]
	#[CompanyPermissionAttribute]
	public static function create($params) {
		try {
			DateHelper::now();
			$companyId = CompanyModel::create($params);
			ReturnHelper::json(['success' => true, 'company_id' => $companyId, 'message' => 'Company created successfully']);
		} catch (ValidationException $e) {
			ReturnHelper::error($e->getMessage(), $e->getErrorCode(), 400);
		} catch (ConflictException $e) {
			ReturnHelper::error($e->getMessage(), $e->getErrorCode(), 409);
		} catch (DatabaseException $e) {
			ReturnHelper::error($e->getMessage(), $e->getErrorCode(), 500);
		} catch (Exception $e) {
			ReturnHelper::error('Internal server error', 'INTERNAL_ERROR', 500);
			error_log("Company creation error: " . $e->getMessage());
		}
	}
	#[LoginAttribute]
	#[CompanyDeletePermissionAttribute]
	public static function delete($params) {
		try {
			DateHelper::now();
			CompanyModel::softDelete($params);
			ReturnHelper::json(['success' => true, 'message' => 'Company deleted successfully']);
		} catch (ValidationException $e) {
			ReturnHelper::error($e->getMessage(), $e->getErrorCode(), 400);
		} catch (NotFoundException $e) {
			ReturnHelper::error($e->getMessage(), $e->getErrorCode(), 404);
		} catch (DatabaseException $e) {
			ReturnHelper::error($e->getMessage(), $e->getErrorCode(), 500);
		} catch (Exception $e) {
			ReturnHelper::error('Internal server error', 'INTERNAL_ERROR', 500);
			error_log("Company deletion error: " . $e->getMessage());
		}
	}
}