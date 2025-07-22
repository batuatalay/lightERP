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
			echo json_encode(['success' => true, 'data' => $companies]);
		} catch (DatabaseException $e) {
			http_response_code(500);
			echo json_encode(['success' => false, 'error' => $e->getMessage(), 'error_code' => $e->getErrorCode()]);
		} catch (Exception $e) {
			http_response_code(500);
			echo json_encode(['success' => false, 'error' => 'Internal server error', 'error_code' => 'INTERNAL_ERROR']);
			error_log("Company getAll error: " . $e->getMessage());
		}
	}
	#[LoginAttribute]
	#[CompanyPermissionAttribute]
	public static function create($params) {
		try {
			DateHelper::now();
			$companyId = CompanyModel::create($params);
			echo json_encode(['success' => true, 'company_id' => $companyId, 'message' => 'Company created successfully']);
		} catch (ValidationException $e) {
			http_response_code(400);
			echo json_encode(['success' => false, 'error' => $e->getMessage(), 'error_code' => $e->getErrorCode()]);
		} catch (ConflictException $e) {
			http_response_code(409);
			echo json_encode(['success' => false, 'error' => $e->getMessage(), 'error_code' => $e->getErrorCode()]);
		} catch (DatabaseException $e) {
			http_response_code(500);
			echo json_encode(['success' => false, 'error' => $e->getMessage(), 'error_code' => $e->getErrorCode()]);
		} catch (Exception $e) {
			http_response_code(500);
			echo json_encode(['success' => false, 'error' => 'Internal server error', 'error_code' => 'INTERNAL_ERROR']);
			error_log("Company creation error: " . $e->getMessage());
		}
	}
	#[LoginAttribute]
	#[CompanyDeletePermissionAttribute]
	public static function delete($params) {
		try {
			DateHelper::now();
			CompanyModel::softDelete($params);
			echo json_encode(['success' => true, 'message' => 'Company deleted successfully']);
		} catch (ValidationException $e) {
			http_response_code(400);
			echo json_encode(['success' => false, 'error' => $e->getMessage(), 'error_code' => $e->getErrorCode()]);
		} catch (NotFoundException $e) {
			http_response_code(404);
			echo json_encode(['success' => false, 'error' => $e->getMessage(), 'error_code' => $e->getErrorCode()]);
		} catch (DatabaseException $e) {
			http_response_code(500);
			echo json_encode(['success' => false, 'error' => $e->getMessage(), 'error_code' => $e->getErrorCode()]);
		} catch (Exception $e) {
			http_response_code(500);
			echo json_encode(['success' => false, 'error' => 'Internal server error', 'error_code' => 'INTERNAL_ERROR']);
			error_log("Company deletion error: " . $e->getMessage());
		}
	}
}