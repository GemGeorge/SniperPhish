<?php

require_once(dirname(__FILE__) . '/db.php');

date_default_timezone_set('UTC');
$entry_time = (new DateTime())->format('d-m-Y h:i A');

//-----------------------------

if(isset($_POST['action_type'])){
	if ($_POST['action_type'] == "send_pwd_reset") sendPwdReset($conn);
	if ($_POST['action_type'] == "do_change_pwd") doChangePwd($conn);
}
else
    die();

//-----------------------------


function sendPwdReset($conn){
	$contact_mail = $_POST['contact_mail'];
	if(isUserExist($conn, $contact_mail))
		if(sendNewReset($conn, $contact_mail)){
			$new_v_hash = md5(uniqid(rand(), true));
			$curr_time = time();
			$stmt = $conn->prepare("UPDATE tb_main SET v_hash=?, v_hash_time=? WHERE contact_mail=?");
			$stmt->bind_param('sss', $new_v_hash,$curr_time,$contact_mail);
			$stmt->execute();
			initResetMail($conn,$new_v_hash,$contact_mail);
		}
		else{
			$stmt = $conn->prepare("SELECT v_hash FROM tb_main WHERE contact_mail = ?");
			$stmt->bind_param("s", $contact_mail);
			$stmt->execute();
			$result = $stmt->get_result()->fetch_assoc();
			initResetMail($conn,$result['v_hash'],$contact_mail);
		}
	echo "success";     //send success irrespectively.
}

function isUserExist($conn, $contact_mail){
	$stmt = $conn->prepare("SELECT v_hash_time FROM tb_main WHERE contact_mail = ?");
	$stmt->bind_param("s", $contact_mail);
	$stmt->execute();
	$result = $stmt->get_result();
	if($result->num_rows > 0)
		return true;
	else
		return false;
}

function sendNewReset($conn, $contact_mail){
	$stmt = $conn->prepare("SELECT v_hash,v_hash_time FROM tb_main WHERE contact_mail = ?");
	$stmt->bind_param("s", $contact_mail);
	$stmt->execute();
	$result = $stmt->get_result()->fetch_assoc();
	if(empty($result['v_hash']) || $result['v_hash_time'] + 86400*2 < time()) //>2 days ==> expired
		return true;
	else
		return false;		
}

function initResetMail($conn, $v_hash, $contact_mail){
	$msg = "Hi,<p>It looks you requested for SnipierPhish password reset. Please visit ".getServerVariable($conn)['baseurl']."/spear/ChangePwd?token=".$v_hash." for resetting password</p>";
	
	$headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";

	mail($contact_mail,"SniperPhish Password Reset",$msg,$headers);
}

function getServerVariable($conn){
	$result = mysqli_query($conn, "SELECT baseurl FROM tb_main_variables");
		if(mysqli_num_rows($result) > 0){
		return mysqli_fetch_all($result, MYSQLI_ASSOC)[0];
	}
}

//-----------------------------------

function doChangePwd($conn){
	if(!(isset($_POST['new_pwd']) && isset($_POST['token'])))
		die("Invalid request");

	$new_pwd_hash = hash("sha256", $_POST['new_pwd'], false);
	$token = $_POST['token'];

	$stmt = $conn->prepare("SELECT COUNT(*) FROM tb_main WHERE v_hash = ?");
	$stmt->bind_param("s", $token);
	$stmt->execute();
	$row = $stmt->get_result()->fetch_row();
	if($row[0] > 0){
		$stmt = $conn->prepare("UPDATE tb_main SET password=?, v_hash=null, v_hash_time=null WHERE v_hash = ?");
		$stmt->bind_param('ss', $new_pwd_hash,$token);
		if ($stmt->execute() === TRUE)
			die('success'); 
		else 
			die("failed"); 	
	}
}
?>