<?php
header('Access-Control-Allow-Origin: *');
if(isset($_GET['debug']) && $_GET['debug']=='on'){ /*Don't set Content-Type*/ }
else{ header('Content-Type: application/json'); }

//sleep(2); //for testing response times
require_once 'inc/config.php';
require_once 'inc/functions/database.php';
require_once 'inc/custom.class.php';
ini_set('display_errors', 'off');
error_reporting(0);

if($_SERVER['REQUEST_METHOD'] == 'GET'){
	$params = $_GET;
}
else if($_SERVER['REQUEST_METHOD'] == "POST"){
	$params = $_POST;
}
$endpoint = $_GET['endpoint'];


$apiVersion = 'v1';
$apiSchema = new getAPISchema();
$apiSchema->init();

$debugData = [];
foreach($apiSchema->response as $r){
	array_push($debugData, array("endpoint"=>$r->methodName, "params"=>explode(',', $r->spParams), "values"=>explode(',', $r->testData) ));
}

if($endpoint=='GenerateToken' && !empty($params['key']) ){
	$db = getDBObject();
	$result = executeStoredProc($db, 'spGenerateToken', array($params['key']));
	if ($result){
		if(isset($_GET['debug']) && $params['debug']=='on'){
			foreach($debugData as $d){
				if($d['endpoint']=='GenerateToken' || $d['endpoint']=='ValidateToken'){ continue; }
				$additional_params = '';
				for($i=0; $i<count($d['params']); $i++){
					if(!empty($d['params'][$i])){
						$additional_params .= '&' . $d['params'][$i] . '=' . urlencode($d['values'][$i]);
					}
				}
				echo '<a href="'.BASE_URL.'/'.$apiVersion.'/'.$d['endpoint'].'/?key='.$params['key'].'&token='.$result[0]->token.'&authcode='.hash('sha512', $params['key'] . PrivateKey . $result[0]->token) . $additional_params.'" target="_blank">Call '.$d['endpoint'].'</a><br /><br />';
			}
		}
		$response = array('result'=>true, 'token'=>$result[0]->token, 'msg'=>'success');
	}
	else{
		$response = array('result'=>false, 'msg'=>'Invalid API Key');
	}
	$db = NULL;
}
else if(!empty($endpoint) && !empty($params['key']) && !empty($params['authcode']) && !empty($params['token']) ){
	$validate = new validateKeys($params['key'] ,$params['authcode'], $params['token']);
	$validate->init();
	if($validate->response==0){ $response = array('result'=>false, 'msg'=>$validate->message); }
	else{
		$methodFound = false;
		foreach($apiSchema->response as $r){
			if($endpoint==$r->methodName){
				$methodFound = true;
				$spParams = explode(',', $r->spParams);
				$paramsValid = true;
				$spParamValues = [];
				foreach($spParams as $p){
					if(!array_key_exists($p, $params)){ $paramsValid = false; }
					else{
						array_push($spParamValues, $params[$p]);
					}
				}
				if($paramsValid || $r->spParams==''){
					$db = getDBObject();
					$result = executeStoredProc($db, $r->spName, $spParamValues);
					//foreach($results as $result){
					if($result){
						$response = new stdClass();
						$response->result = true;
						$response->msg = 'success';
						$response->data = array();
						foreach($result as $row){
							$rowValues = array();
							foreach ($row as $k=>$v){
								$rowValues[$k] = $v;
							}
							if($rowValues['error']==1){
								$response = array('result'=>false, 'error'=>1, 'msg'=>$rowValues['message']);
							}
							else{
								array_push($response->data, $rowValues);
							}
						}
					}
					if(empty($response)){
						$response = array('result'=>false, 'msg'=>'No Results');
					}
				}
				else{
					$response = array('result'=>false, 'msg'=>'Invalid Parameters');
				}
				break;
			}
		}
		if(!$methodFound){
			$response = array('result'=>false, 'msg'=>'Invalid Method');
		}
	}
}
else{
	$response = array('result'=>false, 'msg'=>'Missing Parameters');
}

echo trim(html_entity_decode(json_encode($response)));

?>