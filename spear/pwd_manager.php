<?php
require_once(dirname(__FILE__) . '/session_manager.php');
require_once(dirname(__FILE__) . '/db.php');
//-----------------------------
date_default_timezone_set('UTC');
$entry_time = (new DateTime())->format('d-m-Y h:i A');
header('Content-Type: application/json');

if (isset($_POST)) {
	$POSTJ = json_decode(file_get_contents('php://input'),true);

	if(isset($POSTJ['action_type'])){
		if ($POSTJ['action_type'] == "send_pwd_reset")
			sendPwdReset($conn, $POSTJ);
		if ($POSTJ['action_type'] == "do_change_pwd")
			doChangePwd($conn, $POSTJ);
	}
}
else
	die();

//-----------------------------

function sendPwdReset($conn, &$POSTJ){
	$contact_mail = $POSTJ['contact_mail'];
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
	echo json_encode(['result' => 'success']);	     //send success irrespectively.
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

	if(!mail($contact_mail,"SniperPhish Password Reset",$msg,$headers))
		die(json_encode(['error' => 'Mail sending failed!']));
}

function getServerVariable($conn){
	$result = mysqli_query($conn, "SELECT baseurl FROM tb_main_variables");
		if(mysqli_num_rows($result) > 0){
		return mysqli_fetch_all($result, MYSQLI_ASSOC)[0];
	}
}

//-----------------------------------

function doChangePwd($conn, &$POSTJ){
	if(!(isset($POSTJ['new_pwd']) && isset($POSTJ['token'])))
		die(json_encode(['error' => 'Invalid request']));

	$new_pwd_hash = hash("sha256", $POSTJ['new_pwd'], false);
	$token = $POSTJ['token'];

	$stmt = $conn->prepare("SELECT COUNT(*) FROM tb_main WHERE v_hash = ?");
	$stmt->bind_param("s", $token);
	$stmt->execute();
	$row = $stmt->get_result()->fetch_row();
	if($row[0] > 0){
		$stmt = $conn->prepare("UPDATE tb_main SET password=?, v_hash=null, v_hash_time=null WHERE v_hash = ?");
		$stmt->bind_param('ss', $new_pwd_hash,$token);
		if ($stmt->execute() === TRUE)
			echo json_encode(['result' => 'success']);
		else 
			echo json_encode(['error' => 'Password change failed!']);	
	}
}
?>