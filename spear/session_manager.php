<?php
require_once(dirname(__FILE__) . '/db.php');
require_once(dirname(__FILE__) . '/common_functions.php');

//-----------------------------

function checkSession($login_page){	
	if(isset($_SESSION['username']))
		setCookieData();
	if(!isset($_SESSION['username']) && $login_page == false){
		header("Location: /spear");
		die();
	}
}
function validateLogin($username,$pwd){	
	global $conn;
	$pwdhash = hash("sha256", $pwd, false);
	$stmt = $conn->prepare("SELECT COUNT(*) FROM tb_main where username=? AND password=?");
	$stmt->bind_param('ss', $username,$pwdhash);
	$stmt->execute();
	$row = $stmt->get_result()->fetch_row();
	if($row[0] > 0){
		setServerVariables($conn);
		startProcess(getOSType($conn));
		return true;
	}
	else
		return false;
}

function setServerVariables($conn){
	$server_protocol = isset($_SERVER['HTTPS'])?'https':'http';
	$baseurl = $server_protocol.'://'.$_SERVER['HTTP_HOST'];
	$stmt = $conn->prepare("UPDATE tb_main_variables SET server_protocol=?, domain =?, baseurl=?");
	$stmt->bind_param('sss', $server_protocol, $_SERVER['HTTP_HOST'], $baseurl);
	
	$stmt->execute();
	$stmt->close();
}

function setCookieData(){
	global $conn;
	$result = mysqli_query($conn, "SELECT report_time_zone,report_time_format FROM tb_main_variables");
	if(mysqli_num_rows($result) > 0){
		$result = mysqli_fetch_assoc($result);
		setcookie("c_data",base64_encode($result['report_time_zone'].','.$result['report_time_format']), ["path" => "/", "samesite" => "strict", "httponly" => false]);
	}
}

?>