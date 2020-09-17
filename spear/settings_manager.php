<?php
//-------------------Session check-----------------------
@ob_start();
session_start();
if(!isset($_SESSION['username']))
	die("Access denied");
//-------------------------------------------------------

require_once(dirname(__FILE__) . '/session_manager.php');

if(isset($_POST['action_type'])){
    if($_POST['action_type'] == "modify_account")
		modifyLogin($conn);
    if($_POST['action_type'] == "modify_user_settings")
		modifyUserSettings($conn);
    if($_POST['action_type'] == "modify_sniperphish_settings")
		modifySniperPhishSettings($conn);
    if($_POST['action_type'] == "get_settings")
		getSetingsValues($conn);

}

//-----------------------------

function modifyLogin($conn){
	$username = $_POST['setting_field_uname'];
	$contact_mail = $_POST['setting_field_mail'];
	$old_pwd = $_POST['setting_field_old_pwd'];
	$new_pwd = $_POST['setting_field_new_pwd'];	
	$old_pwd_hash = hash("sha256", $old_pwd, false);

	$stmt = $conn->prepare("SELECT COUNT(*) FROM tb_main WHERE username=? AND password=?");
	$stmt->bind_param('ss', $username, $old_pwd_hash);
	$stmt->execute();
	$row = $stmt->get_result()->fetch_row();
	if($row[0] == 0)
		die("Old password incorrect"); 

	if($new_pwd == ''){	//update email only
		$stmt = $conn->prepare("UPDATE tb_main SET contact_mail=? WHERE username=?");
		$stmt->bind_param('ss', $contact_mail,$username);
		if ($stmt->execute() === TRUE)
			die('success'); 
		else 
			die("Contact mail update failed!"); 
	}
	else{	//update all
		$new_pwd_hash = hash("sha256", $new_pwd, false);	
		$stmt = $conn->prepare("UPDATE tb_main SET password=?, contact_mail=? WHERE username=?");
		$stmt->bind_param('sss', $new_pwd_hash,$contact_mail,$username);
		if ($stmt->execute() === TRUE)
			die('success'); 
		else 
			die("Update failed!"); 
	}
}

function modifyUserSettings($conn){	
	$timezone_format = $_POST['timezone_format'];
	$date_time_fromat = $_POST['date_fromat'].','.$_POST['space_format'].','.$_POST['time_format'];
	$stmt = $conn->prepare("UPDATE tb_main_variables SET report_time_zone=?, report_time_format =?");
	$stmt->bind_param('ss', $timezone_format, $date_time_fromat);
	
	if ($stmt->execute() === TRUE)
		die('success'); 
	else 
		die("failed"); 
	$stmt->close();
}
function modifySniperPhishSettings($conn){
	$timezone_format = $_POST['timezone_format'];
	$stmt = $conn->prepare("UPDATE tb_main_variables SET sniperphish_time_zone	=?");
	$stmt->bind_param('s', $timezone_format);
	
	if ($stmt->execute() === TRUE)
		die('success'); 
	else 
		die("failed"); 
	$stmt->close();
}

function getSetingsValues($conn){
	$result = mysqli_query($conn, "SELECT report_time_zone,report_time_format,sniperphish_time_zone FROM tb_main_variables");
	if(mysqli_num_rows($result) > 0){
		$result = mysqli_fetch_assoc($result);

		$result1 = mysqli_query($conn, "SELECT contact_mail FROM tb_main");
		$result1 = mysqli_fetch_assoc($result1);

		$result['contact_mail'] = $result1['contact_mail'];

		header('Content-Type: application/json');
		echo json_encode($result);
	}
}

?>