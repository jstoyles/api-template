<?php

function getDBObject()
{
	try
	{
		$db = new PDO(DB_TYPE .  ":host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASSWORD, array(PDO::ATTR_PERSISTENT=>true));
	}
	catch (PDOException $e)
	{
		$response = array('result'=>'false', 'msg'=>$e->getMessage());
		echo(json_encode($response));
		die();
	}
	
	return $db;
}

function executeStoredProc($db, $proc_name, $params = NULL, $debug = false)
{
	$sql = 'CALL '. $proc_name;

	if ($params)
	{
		foreach ($params as $param)
		{
			$param = trim($param);
			$safe_params[] = $db->quote($param);
		}

		$safe_params = implode(', ', $safe_params);
	}

	if(empty($safe_params)){
		$sql .= "()";
	}
	else{
		$sql .= "(" . $safe_params . ")";
	}

	if ($debug)
	{
		echo ($sql);
		die();
	}

	$query = $db->prepare($sql);
	$query->execute();
	$query->setFetchMode(PDO::FETCH_OBJ);

	$result = $query->fetchAll();

	return $result;
}

function executeFunction($db, $function_name, $params = NULL, $debug = false)
{
	$sql = 'SELECT '. $function_name;

	if ($params)
	{
		foreach ($params as $param)
		{
			$param = trim($param);
			$safe_params[] = $db->quote($param);
		}

		$safe_params = implode(', ', $safe_params);
	}

	$sql .= "(" . $safe_params . ")";

	if ($debug)
	{
		echo ($sql);
		die();
	}

	$query = $db->prepare($sql);
	$query->execute();

	$result = $query->fetchColumn();

	return $result;
}

?>