<?php 
require_once BASE . "/middleware/common.middleware.php";
require_once BASE . "/model/company.model.php";


class Company extends SimpleController{

	public static function getAll () {
		$companies = CompanyModel::getAllCompaniesWithContacts();
		var_dump($companies);exit;
	
	}

	public static function testFunction2() {
		echo PHP_EOL . 'test Function 2';
		
	}
}