<?php

class ReturnHelper {

	public static function success($message) {
		$json = [
			'code' => '200',
			'message' => $message
		];
		echo json_encode($json);
	}

	public static function fail($message) {
		$json = [
			'code' => '400',
			'message' => $message
		];
		echo json_encode($json);
	}
}