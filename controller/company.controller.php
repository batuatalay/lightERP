<?php 
require_once BASE . "/middleware/common.middleware.php";
require_once BASE . "/model/company.model.php";
require_once BASE . "/helper/date.helper.php";
require_once BASE . "/helper/return.helper.php";


require_once BASE . "/middleware/company/company.middleware.php";

class Company extends SimpleController{

	public static function getAll () {
		$companies = CompanyModel::getAllCompaniesWithContacts();
		var_dump($companies);exit;
	
	}
	#[LoginAttribute]
	#[CompanyPermissionAttribute]
	public static function create($params) {
		DateHelper::now();
		CompanyModel::create($params);
	}
	#[LoginAttribute]
	#[CompanyDeletePermissionAttribute]
	public static function delete($params) {
		DateHelper::now();
		CompanyModel::softDelete($params);
	}
}