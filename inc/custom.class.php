<?php
//Custom classes can be placed here...
//error_reporting(0);

class validateKeys{
	public $message = NULL;
	public $response = NULL;

	public function __construct($api_key, $auth_code, $token){
		$this->api_key = $api_key;
		$this->auth_code = $auth_code;
		$this->token = $token;
	}
	private function __clone(){} // Make private to block from cloning

	public function init() {
		$db = getDBObject();

		$result = executeStoredProc($db, 'spValidateToken', array($this->token, $this->api_key, $this->auth_code));
		if ($result){
			$this->response = $result[0]->response;
			$this->message = $result[0]->message;
		}
		else{
			$this->response = 0;
				$this->message = 'Invalid Token';
		}
		$db = NULL;
	}
}

class getAPISchema{
	public $message = NULL;
	public $response = NULL;

	public function init(){
		$db = getDBObject();

		$result = executeStoredProc($db, '__spGetAPIMethodSchema', array());
		$this->response = $result;
		$db = NULL;
	}
}
?>
