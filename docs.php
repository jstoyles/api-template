 <!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
<title>API Docs</title>
<style>
body{
	font-family:Arial;
}

code {
	padding:2px 4px;
	font-size:90%;
	color:#c7254e;
	background-color:#f9f2f4;
	border-radius:4px;
}

.main-table{ width:calc(100vw - 40px); }
.main-table td{ padding:20px; border:1px solid #ddd; }
.main-table thead td{ border-width:6px 1px 1px 1px }

.request-table, .main-table{
	border:1px solid #ccc;
	border-collapse:collapse;
	margin:0 10px;
}
.request-table td{
	border:1px solid #ccc;
	padding:5px;
	font-size:10pt;
}
.request-table thead td, .main-table thead td{
	background-color:#f3f3f3;
	font-weight:bold;
	color:#333;
}

.block{
	display:block;
}

.padding-10{
	padding:10px;
}
.padding-edges{
	padding:10px 20px 20px 20px;
}

.method-wrapper{
	/*display:none;*/
	margin:0px 0 20px 0;
}

.request-header, .response-header {
	border: 1px solid #ccc;
	border-top-width: 1px;
	border-right-width: 1px;
	border-bottom-width: 1px;
	border-left-width: 1px;
	border-width: 0 1px 1px 0;
	border-radius: 6px 0 6px 0;
	color: #999;
	background-color: #f3f3f3;
	padding: 4px 12px;
	margin: 0;
	display: inline-block;
}

.request-wrapper, .response-wrapper{
	border:1px solid #ccc;
	border-radius:6px;
}
.response-wrapper{
	margin-top:20px;
}

hr{
	display:block;
	border:0;
	height:0;
	border-top:1px dashed #999;
	margin:10px 10px 20px 10px;
}

.method-name{
	padding:10px;
}

.endpoint{
	padding:10px;
}

.description{
	padding:10px;
	border:1px solid #ccc;
	background-color:#f3f3f3;
	margin:0 10px;
	font-size:10pt;
}

.parameters{
	padding:10px;
	font-weight:bold;
	margin-top:20px;
}

.example{
	padding:10px;
}

.example-response-header{
	font-weight:bold;
	margin-left:10px;
}

#example-response {
	font-family: "Lucida Console", "Monaco", monospace;
	font-size:8pt;
	line-height:1.2;
	border:1px solid #ccc;
	background-color:#f9f9f9;
	padding:10px;
	margin:10px 10px 0 10px;
}

.instructions{
	margin-top:0;
	color:#999;
	text-transform:uppercase;
	font-size:10pt;
}

.method-link{
	display:inline-block;
	font-size:10pt;
	color:#0066cc;
	width:248px;
	white-space:nowrap;
	overflow:hidden;
	text-overflow:ellipsis;
}
</style>

</head>
<body>

<?php
header('Access-Control-Allow-Origin: *');

//sleep(2); //for testing response times
require_once 'inc/config.php';
require_once 'inc/functions/database.php';
require_once 'inc/custom.class.php';
ini_set('display_errors', 'on');
error_reporting(E_ALL);

$apiSchema = new getAPISchema();
$apiSchema->init();
?>

<center>

<table class="main-table">
	<thead>
		<tr>
			<td>API METHODS</td>
			<td>API METHOD DOCUMENTATION</td>
		</tr>
	</thead>

<tbody>
	<tr>
		<td valign="top" style="width:250px">
			<p class="instructions">Select an API method</p>
			<?php
			foreach($apiSchema->response as $a){
				if($a->method == 'ValidateToken'){ continue; }
				$params = explode(',', $a->parameters);
				$param_types = explode(',', $a->parameter_data_types);
				$additional_params = '';
			?>
			<a class="method-link" href="?method=<?=$a->method?>"><?=$a->method?></a>
			<hr style="margin:10px 0;" />
			<?php } ?>
		</td>
		<td valign="top">
			<?php
			$method_counter = 0;
			if(!empty($_GET['method'])){
				foreach($apiSchema->response as $a){
					if($a->method == 'ValidateToken'){ continue; }
					if($a->method != $_GET['method']){ continue; }
					$params = explode(',', $a->parameters);
					$param_types = explode(',', $a->parameter_data_types);
					$additional_params = '';
					$comments = explode('~', $a->comments);
			?>
			<div class="method-wrapper">
				<div class="request-wrapper">
					<h5 class="request-header">Request</h5>

					<div class="padding-edges">
						<span class="method-name block">
							<b><u>Method Name</u>: <?=$a->method?></b>
						</span>

						<span class="endpoint block">
							Endpoint: <code><?=BASE_URL?>/<?=VERSION?>/<?=$a->method?>/</code><br />
							Request Type(s): <code>GET or POST</code><br />
							Content-Type: <code>application/x-www-form-urlencoded</code>
						</span>

						<hr />

						<span class="description block">
							<b>DESCRIPTION:</b> <?=trim($comments[0])?>
						</span>

						<span class="parameters block">Required Parameters</span>
						<span class="parameters-list">
							<table class="request-table">
								<thead>
									<tr>
										<td>Parameter</td>
										<td>Type</td>
										<td>Description</td>
									</tr>
								</thead>
								<tbody>
									<tr>
										<td>key</td>
										<td>String</td>
										<td>Your API Key</td>
									</tr>
									<tr>
										<td>token</td>
										<td>String</td>
										<td>The token generated from the GenerateToken method</td>
									</tr>
									<tr>
										<td>authcode</td>
										<td>String</td>
										<td>A SHA512 encrypted string combination of your <b>Public API Key</b> + <b>Private API Key</b> + <b>Token</b></td>
									</tr>
								<?php
								for($p=0; $p<count($params); $p++){
									if($params[$p] == 'public_key' || $params[$p] == ''){ continue; }
									//$param_description = ucwords(str_replace('id', 'ID', str_replace('_', ' ', $params[$p])));
									$param_description = strtoupper(str_replace('_', ' ', $params[$p]));
								?>
									<tr>
										<td><?=$params[$p]?></td>
										<td><?=$param_types[$p]?></td>
										<td>The <b><u><?=$param_description?></u></b> you want to retrieve data for</td>
									</tr>
								<?php
								}
								?>
								</tbody>
							</table>
						</span>
					</div>
				</div>

				<div class="response-wrapper">
					<h5 class="response-header">Response</h5>

					<div class="padding-edges">
						<span class="block padding-10">
							Content-Type: <code>application/json</code>
						</span>

						<hr />

						<span class="example-response-header block">Example Response</span>

						<pre id="example-response"></pre>
						<script>
							var markup = <?=isset($comments[1])?trim($comments[1]):'{"Configuration Error":"Example Not Provided"}'?>;
							document.getElementById('example-response').innerText = JSON.stringify(markup, undefined, 4);
						</script>
					</div>
				</div>
			</div>
			<?php
				$method_counter++;
				}
			}
			if($method_counter==0){
			?>
			<p class="instructions">No Valid Method Selected</p>
			<?php } ?>
			</td>
		</tr>
	</tbody>
</table>

</center>

</body>
</html>
