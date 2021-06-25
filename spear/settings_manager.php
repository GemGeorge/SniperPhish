<?php
require_once(dirname(__FILE__) . '/session_manager.php');
if(isSessionValid() == false)
	die("Access denied");
//-------------------------------------------------------
header('Content-Type: application/json');

if (isset($_POST)) {
	$POSTJ = json_decode(file_get_contents('php://input'),true);

	if(isset($POSTJ['action_type'])){
		if($POSTJ['action_type'] == "modify_account")
			modifyLogin($conn,$POSTJ);
		if($POSTJ['action_type'] == "modify_user_settings")
			modifyUserSettings($conn, json_encode($POSTJ['time_zone']), json_encode($POSTJ['time_format']));
		if($POSTJ['action_type'] == "get_settings")
			getSetingsValues($conn);

		//Store data
		if($POSTJ['action_type'] == "get_store_list")
			getStoreList($conn, $POSTJ['type'], (isset($POSTJ['name'])?$POSTJ['name']:""));
	}
}

//-----------------------------

function modifyLogin($conn,&$POSTJ){
	$username = $POSTJ['setting_field_uname'];
	$contact_mail = $POSTJ['setting_field_mail'];
	$old_pwd = $POSTJ['setting_field_old_pwd'];
	$new_pwd = $POSTJ['setting_field_new_pwd'];	
	$old_pwd_hash = hash("sha256", $old_pwd, false);

	$stmt = $conn->prepare("SELECT COUNT(*) FROM tb_main WHERE username=? AND password=?");
	$stmt->bind_param('ss', $username, $old_pwd_hash);
	$stmt->execute();
	$row = $stmt->get_result()->fetch_row();
	if($row[0] > 0){	//old password is correct
		if($new_pwd == ''){	//update email only
			$stmt = $conn->prepare("UPDATE tb_main SET contact_mail=? WHERE username=?");
			$stmt->bind_param('ss', $contact_mail,$username);
			if ($stmt->execute() === TRUE)
				echo(json_encode(['result' => 'success']));	
			else 
				echo(json_encode(['result' => 'failed', 'error' => 'Contact mail update failed!']));
		}
		else{	//update all
			$new_pwd_hash = hash("sha256", $new_pwd, false);	
			$stmt = $conn->prepare("UPDATE tb_main SET password=?, contact_mail=? WHERE username=?");
			$stmt->bind_param('sss', $new_pwd_hash,$contact_mail,$username);
			if ($stmt->execute() === TRUE)
				echo(json_encode(['result' => 'success']));	
			else 
				echo(json_encode(['result' => 'failed', 'error' => 'Update failed!']));
		}
	}
	else
		echo(json_encode(['result' => 'failed', 'error' => 'Old password incorrect!']));
}

function modifyUserSettings($conn, $time_zone, $time_format){
	$stmt = $conn->prepare("UPDATE tb_main_variables SET time_zone=?, time_format =? where id=1");
	$stmt->bind_param('ss', $time_zone, $time_format);
	
	if ($stmt->execute() === TRUE){
		echo(json_encode(['result' => 'success']));	
		setInfoCookie();	//refresh c_data cookie
	}
	else 
		echo(json_encode(['result' => 'failed', 'error' => 'Update failed!']));	
	$stmt->close();
}

function getSetingsValues($conn){
	$result1 = mysqli_query($conn, "SELECT time_zone,time_format FROM tb_main_variables")->fetch_assoc();
	$result2 = mysqli_query($conn, "SELECT contact_mail FROM tb_main")->fetch_assoc();
	$result1['time_zone'] = json_decode($result1['time_zone']);
	$result1['time_format'] = json_decode($result1['time_format']);
	echo json_encode(array_merge($result1,$result2));
}

//---------------Store Section Start------------------------------------
function getStoreList($conn, $type, $name){
	$resp = [];

	if($type == "mail_sender"){
		$stmt = $conn->prepare("SELECT name,info,content FROM tb_store WHERE type = ?");
		$stmt->bind_param("s", $type);
		$stmt->execute();
		$result = $stmt->get_result();
		$rows = $result->fetch_all(MYSQLI_ASSOC);
		foreach($rows as $i => $row)
			$resp[$row['name']] = ["info" => json_decode($row['info']), "content" => json_decode($row['content'])];
		echo json_encode($resp);
	}

	if($type == "mail_template"){
		if(empty($name)){
			$stmt = $conn->prepare("SELECT name,info FROM tb_store WHERE type = ?");
			$stmt->bind_param("s", $type);
			$stmt->execute();
			$result = $stmt->get_result();
			$result = $result->fetch_all(MYSQLI_ASSOC);
			foreach($result as $row)
				$resp[$row['name']] = json_decode($row['info']);
			echo json_encode($resp);
		}
		else{
			$stmt = $conn->prepare("SELECT content FROM tb_store WHERE type = ? AND name = ?");
			$stmt->bind_param("ss", $type, $name);
			$stmt->execute();
			$result = $stmt->get_result();
			if($result->num_rows != 0){
				$row = $result->fetch_assoc();
				echo $row['content'];
			}
		}
	}
}

?>