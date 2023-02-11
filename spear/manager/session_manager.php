<?php
if (session_status() === PHP_SESSION_NONE) {
   @ob_start();
   session_start();
   session_write_close();	//prevent access denied lock error
}
if (file_exists(dirname(__FILE__,2) . '/config/db.php'))
	require_once(dirname(__FILE__,2) . '/config/db.php');
else
	die("Can not find db.php. Visit <a href='/install'>here</a> to install SniperPhish");	//shows if login page is opened before install 
require_once(dirname(__FILE__) . '/common_functions.php');
date_default_timezone_set('UTC');
$entry_time = (new DateTime())->format('d-m-Y h:i A');
error_reporting(E_ERROR | E_PARSE); //Disable warnings
//-----------------------------

function validateLogin($username,$pwd){	
	global $conn;
	$pwdhash = hash("sha256", $pwd, false);
	$stmt = $conn->prepare("SELECT COUNT(*) FROM tb_main where username=? AND password=?");
	$stmt->bind_param('ss', $username,$pwdhash);
	$stmt->execute();
	$row = $stmt->get_result()->fetch_row();
	if($row[0] > 0){
		updateLoginLogout($conn, $username, $GLOBALS['entry_time'], true);
		$os = getOSType();
		if(!isProcessRunning($conn,$os))
			startProcess($os);
		return true;
	}
	else
		return false;
}

function isSessionValid($f_redirection=false){	//this check refreshes session expiry
	if (isset($_SESSION['username'])) {
		createSession(false,$_SESSION['username']);
		return true;
	}
	else{
		terminateSession($f_redirection); //redirect to home if true
		return false;
	}
}

function setInfoCookie(&$conn, &$username){
	$row = getLoginLogoutInfo($conn, $username);
	$DTime_info = getTimeInfo($conn);
	$row['timezone'] = $DTime_info['time_zone']['timezone'];
	$last_login_hist = json_decode($row['last_login']);
	$last_login = ($last_login_hist==null || count($last_login_hist)==1)?$last_login_hist[0]:$last_login_hist[1];
	$row['last_login'] = getInClientTime_FD($DTime_info,$last_login,null,'d-m-Y h:i A');
	if($row['last_login'] == '-') //first time login
		$row['last_login'] = getInClientTime_FD($DTime_info,(new DateTime())->format('d-m-Y h:i A'),null,'d-m-Y h:i A');
	setcookie("c_data",base64_encode(json_encode($row)), ["path" => "/", "SameSite" => "Strict", "HttpOnly" => false]);
}

function updateLoginLogout($conn, $username, $entry_time, $islogin){
	$access_info = getLoginLogoutInfo($conn, $username);

	if($islogin){
		$login_logout_hist = getUpdatedLoginLogoutHist($entry_time, json_decode($access_info['last_login']));
		$stmt = $conn->prepare("UPDATE tb_main SET last_login=? WHERE username=?");
		logIt('Account login',$username);
	}
	else{
		$login_logout_hist = getUpdatedLoginLogoutHist($entry_time, json_decode($access_info['last_logout']));
		$stmt = $conn->prepare("UPDATE tb_main SET last_logout=? WHERE username=?");
		logIt("Account logout");
	}
	$stmt->bind_param('ss', json_encode($login_logout_hist),$username);
	$stmt->execute();
}

function getUpdatedLoginLogoutHist(&$entry_time, &$login_logout_hist){
	if($login_logout_hist==null)
		$login_logout_hist[0]=$entry_time;
	elseif(count($login_logout_hist) == 1)
		$login_logout_hist[1]=$entry_time;
	else{
		$login_logout_hist[0]=$login_logout_hist[1];
		$login_logout_hist[1]=$entry_time;
	}
	return $login_logout_hist;
}

function getLoginLogoutInfo(&$conn, &$username){
	$stmt = $conn->prepare("SELECT name,username,dp_name,last_login,last_logout FROM tb_main WHERE username=?");
	$stmt->bind_param('s', $username);
	$stmt->execute();
	$result = $stmt->get_result();
	if($result->num_rows > 0)
		return $result->fetch_assoc();
}

//-----------------------------------Start Public Access-------------------------------
function amIPublic($tk_id,$campaign_id,$tracker_id=""){
	global $conn;

	if(empty($tracker_id))
		$ctrl_ids = json_encode([$campaign_id]);
	else
		$ctrl_ids = json_encode([$campaign_id,$tracker_id]);

	$stmt = $conn->prepare("SELECT COUNT(*) FROM tb_access_ctrl WHERE tk_id=? AND ctrl_ids=?");
	$stmt->bind_param("ss", $tk_id,$ctrl_ids);
	$stmt->execute();
	$row = $stmt->get_result()->fetch_row();
	if($row[0] > 0)
		return true;
	else
		return false;
}

//====================================

if (isset($_POST)) {
	$POSTJ = json_decode(file_get_contents('php://input'),true);

	if(isset($POSTJ['action_type'])){ 

		if(isset($POSTJ['tk_id']))
			if($POSTJ['action_type'] == "manage_dashboard_access"){
				if(isset($POSTJ['campaign_id']) && isset($POSTJ['tracker_id']))
					manageDashboardAccess($POSTJ['tk_id'],$POSTJ['ctrl_val'],$POSTJ['campaign_id'],$POSTJ['tracker_id']);
				else
					if(isset($POSTJ['campaign_id']))
						manageDashboardAccess($POSTJ['tk_id'],$POSTJ['ctrl_val'],$POSTJ['campaign_id']);
			}
			else
			if($POSTJ['action_type'] == "get_access_info"){
				if(isset($POSTJ['campaign_id']) && isset($POSTJ['tracker_id']))
					getAccessInfo($POSTJ['tk_id'],$POSTJ['campaign_id'],$POSTJ['tracker_id']);
				else
					if(isset($POSTJ['campaign_id']))
						getAccessInfo($POSTJ['tk_id'],$POSTJ['campaign_id']);
			}

		if($POSTJ['action_type'] == "re_login")
			doReLogin($POSTJ['username'], $POSTJ['pwd']);
		if($POSTJ['action_type'] == "terminate_session")
			terminateSession(false);
	}
}

function manageDashboardAccess($tk_id,$ctrl_val,$campaign_id,$tracker_id=""){	// For web-email camp
	header('Content-Type: application/json');
	global $conn;

	if(empty($tracker_id))
		$ctrl_ids = json_encode([$campaign_id]);
	else
		$ctrl_ids = json_encode([$campaign_id,$tracker_id]);

	//delete existing entry
	$stmt = $conn->prepare("DELETE FROM tb_access_ctrl WHERE ctrl_ids = ?");
	$stmt->bind_param("s", $ctrl_ids);
	$stmt->execute();
	$stmt->close();

	if($ctrl_val == true){
		$stmt = $conn->prepare("INSERT INTO tb_access_ctrl(tk_id,ctrl_ids) VALUES(?,?)");
		$stmt->bind_param('ss', $tk_id,$ctrl_ids);
	}
	else{
		$stmt = $conn->prepare("DELETE FROM tb_access_ctrl WHERE tk_id = ?");
		$stmt->bind_param('s', $tk_id);
	}

	if ($stmt->execute() === TRUE)
		echo json_encode(['result' => 'success', 'tk_id'=> $tk_id]);	
	else 
		echo json_encode(['result' => 'failed', 'error' => 'Error in enabling/disabling access']);	
	$stmt->close();
}

function getAccessInfo($tk_id, $campaign_id, $tracker_id=""){
	header('Content-Type: application/json');
	global $conn;
	
	if(empty($tracker_id))
		$ctrl_ids = json_encode([$campaign_id]);
	else
		$ctrl_ids = json_encode([$campaign_id,$tracker_id]);

	$stmt = $conn->prepare("SELECT tk_id FROM tb_access_ctrl WHERE ctrl_ids=?");
	$stmt->bind_param("s", $ctrl_ids);
	$stmt->execute();
	$result = $stmt->get_result();
	if($row = $result->fetch_assoc())
		echo json_encode(['pub_access' => true, 'tk_id'=>$row["tk_id"]]);
	else
		echo json_encode(['pub_access' => false]);
	$stmt->close();
}
//-----------------------------------End Public Access-------------------------------

function doReLogin($username, $pwd){
	header('Content-Type: application/json');
	global $conn;

	$pwdhash = hash("sha256", $pwd, false);
	$stmt = $conn->prepare("SELECT COUNT(*) FROM tb_main where username=? AND password=?");
	$stmt->bind_param('ss', $username,$pwdhash);
	$stmt->execute();
	$row = $stmt->get_result()->fetch_row();
	if($row[0] > 0){
		createSession(true,$username);
		echo json_encode(['result' => 'success']);	
	}
	else
		echo json_encode(['result' => 'failed']);	
}

function createSession($f_regenerate,$username){
	global $conn;
	session_destroy();
	session_set_cookie_params([
			'lifetime' => 86400,	//86400=1 day
			'secure' => false,
			'httponly' => true,
			'samesite' => 'Strict'
		]);

	session_start();

	if($f_regenerate){
		header_remove('Set-Cookie');	//deletes header set by session_start(); from response
		session_regenerate_id(true);
	}

	$_SESSION['username'] = $username;
	setInfoCookie($conn,$username);
}

function terminateSession($redirection=true){
	session_destroy();
	if($redirection){
		ob_end_clean();   // clear output buffer
		header("Location: /spear/");
		die();
	}
}
?>