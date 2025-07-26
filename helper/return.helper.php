<?php

class ReturnHelper {

	public static function success($message, $data = null) {
		$json = [
			'code' => '200',
			'message' => $message
		];
		if ($data !== null) {
			$json['data'] = $data;
		}
		echo json_encode($json);
	}

	public static function fail($message, $errorCode = null) {
		$json = [
			'code' => '400',
			'message' => $message
		];
		if ($errorCode !== null) {
			$json['error_code'] = $errorCode;
		}
		echo json_encode($json);
	}

	public static function json($data, $statusCode = 200) {
		http_response_code($statusCode);
		echo json_encode($data);
	}

	public static function error($message, $errorCode = null, $statusCode = 500) {
		http_response_code($statusCode);
		$json = [
			'success' => false,
			'error' => $message
		];
		if ($errorCode !== null) {
			$json['error_code'] = $errorCode;
		}
		echo json_encode($json);
	}

	public static function successWithData($data, $message = null, $statusCode = 200) {
		http_response_code($statusCode);
		$json = ['success' => true, 'data' => $data];
		if ($message !== null) {
			$json['message'] = $message;
		}
		echo json_encode($json);
	}
}