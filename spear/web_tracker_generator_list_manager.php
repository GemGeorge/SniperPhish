<?php
//-------------------Session check-----------------------
@ob_start();
session_start();
if(!isset($_SESSION['username']))
	die("Access denied");
//-------------------------------------------------------

require_once(dirname(__FILE__) . '/session_manager.php');
date_default_timezone_set('UTC');
$entry_time = (new DateTime())->format('d-m-Y h:i A');

if(isset($_POST['action_type'])){
    if($_POST['action_type'] == "save_web_tracker")
		saveWebTracker($conn);
    if($_POST['action_type'] == "get_web_tracker_list")
		getWebTrackerList($conn);
    if($_POST['action_type'] == "get_web_tracker_from_id")
		getWebTrackerFromId($conn);
	if($_POST['action_type'] == "get_web_tracker_list_for_modal")
		getWebTrackerListForModal($conn);
	if($_POST['action_type'] == "pause_stop_web_tracker_tracking")
		pauseStopWebTrackerTracking($conn, $_POST['active'], $_POST['tracker_id']);
	if($_POST['action_type'] == "make_copy_web_tracker")
		makeCopyWebTracker($conn);
    if($_POST['action_type'] == "delete_web_tracker")
		deleteWebTracker($conn);

	if($_POST['action_type'] == "get_html_content")
		getHTMLContent($conn);


	if($_POST['action_type'] == "get_web_tracker_codes")
		getWebTrackerCodes($conn);

	if($_POST['action_type'] == "get_starting_mail_campaign_list")
		getStartingMailCampaignList($conn);
}
else
    die();

//-----------------------------
function getHTMLContent(){
	$url=$_POST['url'];
	$ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:79.0) Gecko/20100101 Firefox/79.0');
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // ignore SSL errors
    $result=curl_exec($ch);
    curl_close($ch);
    if($result)
    	echo $result;
    else
    	echo "error";
}
function getWebTrackerCodes($conn){
	if(!isset($_POST['tracker_id']))
		die("Missing tracker id");
	$tracker_id = $_POST['tracker_id'];
	
	$stmt = $conn->prepare("SELECT content_html,tracker_step_data FROM tb_core_web_tracker_list WHERE tracker_id = ?");
	$stmt->bind_param("s", $tracker_id);
	$stmt->execute();
	$result = $stmt->get_result();
	header('Content-Type: application/json');
	if($result->num_rows != 0){
		$row = $result->fetch_assoc() ;
		echo json_encode($row) ;
	}			
	$stmt->close();
}

function pauseStopWebTrackerTracking($conn,$active,$tracker_id){

	if($active == 0){ //stopping
		$stmt = $conn->prepare("UPDATE tb_core_web_tracker_list SET active=?, stop_time=? where tracker_id=?");
		$stmt->bind_param('sss', $active,$GLOBALS['entry_time'],$tracker_id);
	}
	else
		if(checkTrackerStartedPreviously($conn,$tracker_id) == true){
			$stmt = $conn->prepare("UPDATE tb_core_web_tracker_list SET active=?  where tracker_id=?");
			$stmt->bind_param('ss', $active,$tracker_id);
		}
		else{
			$stmt = $conn->prepare("UPDATE tb_core_web_tracker_list SET active=?, start_time=? where tracker_id=?");
			$stmt->bind_param('sss', $active,$GLOBALS['entry_time'],$tracker_id);
		}

	if ($stmt->execute() === TRUE)
		die('success'); 
	else 
		die("failed"); 		
}

function checkTrackerStartedPreviously($conn,$tracker_id){
	$stmt = $conn->prepare("SELECT start_time FROM tb_core_web_tracker_list where tracker_id = ?");
	$stmt->bind_param("s", $tracker_id);
	$stmt->execute();
	$row = $stmt->get_result()->fetch_assoc();
	
	if($row['start_time'] == "")
		return false;
	else
		return true;
}

function saveWebTracker($conn) { 
	$tracker_step_data_string = base64_decode($_POST['tracker_step_data']);
	$tracker_step_data = json_decode($tracker_step_data_string,true);
	$tracker_code_output = json_decode(base64_decode($_POST['tracker_code_output']),true);
	$tracker_name = $tracker_step_data['start']['tb_tracker_name'];
	$active = $tracker_step_data['start']['cb_auto_ativate'] == true ? 1 : 0;
	$tracker_id = $_POST['tracker_id'];
	$content_html = json_encode($tracker_code_output["web_forms_code"]);
	$content_js = $tracker_code_output["js_tracker"];

	if(checkWebTrackerIdExist($conn,$tracker_id)){
		$stmt = $conn->prepare("UPDATE tb_core_web_tracker_list SET tracker_name=?, content_html=?, content_js=?, tracker_step_data=?, active=? WHERE tracker_id=?");
		$stmt->bind_param('ssssss', $tracker_name,$content_html,$content_js,$tracker_step_data_string,$active,$tracker_id);
	}
	else{
		$stmt = $conn->prepare("INSERT INTO tb_core_web_tracker_list(tracker_id,tracker_name,content_html,content_js,tracker_step_data,active,date) VALUES(?,?,?,?,?,?,?)");
		$stmt->bind_param('sssssss', $tracker_id,$tracker_name,$content_html,$content_js,$tracker_step_data_string,$active,$GLOBALS['entry_time']);
	}
	
	if($stmt->execute() === TRUE)
		pauseStopWebTrackerTracking($conn,$active,$tracker_id);
	else 
		die("failed"); 
}

function checkWebTrackerIdExist($conn,$tracker_id){
	$stmt = $conn->prepare("SELECT COUNT(*) FROM tb_core_web_tracker_list where tracker_id = ?");
	$stmt->bind_param("s", $tracker_id);
	$stmt->execute();
	$row = $stmt->get_result()->fetch_row();
	if($row[0] > 0)
		return true;
	else
		return false;
}

function getWebTrackerList($conn){	
	header('Content-Type: application/json');
	$result = mysqli_query($conn, "SELECT tracker_id,tracker_name,tracker_step_data,date,start_time,stop_time,active FROM tb_core_web_tracker_list");
	if(mysqli_num_rows($result) > 0)
		echo json_encode(mysqli_fetch_all($result, MYSQLI_ASSOC));
	else
		echo json_encode(['resp' => 'No data']);	
}

function getWebTrackerListForModal($conn){	
	header('Content-Type: application/json');
	$result = mysqli_query($conn, "SELECT tracker_id,tracker_name,date FROM tb_core_web_tracker_list");
	if(mysqli_num_rows($result) > 0)
		echo json_encode(mysqli_fetch_all($result, MYSQLI_ASSOC));
	else
		echo json_encode(['resp' => 'No data']);	
}

function getWebTrackerFromId($conn){
	if(!isset($_POST['tracker_id']))
		die("Missing tracker id");
	$tracker_id = $_POST['tracker_id'];
	
	$stmt = $conn->prepare("SELECT * FROM tb_core_web_tracker_list where tracker_id = ?");
	$stmt->bind_param("s", $tracker_id);
	$stmt->execute();
	$result = $stmt->get_result();
	header('Content-Type: application/json');
	if($result->num_rows != 0){
		$row = $result->fetch_assoc() ;
		echo json_encode($row) ;
	}			
	$stmt->close();
	
}

function makeCopyWebTracker($conn){
	$old_tracker_id = $_POST['tracker_id'];
	$new_tracker_id = $_POST['new_tracker_id'];
	$new_tracker_name = $_POST['new_tracker_name'];

	$stmt = $conn->prepare("INSERT INTO tb_core_web_tracker_list (tracker_id,tracker_name,content_html,content_js,tracker_step_data,date,active) SELECT ?, ?, content_html,content_js,tracker_step_data,?,0 FROM tb_core_web_tracker_list WHERE tracker_id=?");
	$stmt->bind_param("ssss", $new_tracker_id, $new_tracker_name, $GLOBALS['entry_time'], $old_tracker_id);
	
	if ($stmt->execute() === TRUE)
			die('success'); 
		else 
			die("failed"); 
	$stmt->close();
}

function deleteWebTracker($conn){
	if(!isset($_POST['tracker_id']))
		die("Missing tracker id");
	$tracker_id = $_POST['tracker_id'];
	
	$stmt = $conn->prepare("DELETE FROM tb_core_web_tracker_list WHERE tracker_id = ?");
	$stmt->bind_param("s", $tracker_id);
	$stmt->execute();
	if($stmt->affected_rows != 0){
		echo "deleted";
		deleteWebTrackerData($conn,$tracker_id);
	}
	else
		echo "error";
	$stmt->close();
}

function deleteWebTrackerData($conn, $tracker_id){
	$stmt = $conn->prepare("DELETE FROM tb_data_webpage_visit WHERE tracker_id = ?");
	$stmt->bind_param("s", $tracker_id);
	$stmt->execute();

	$stmt = $conn->prepare("DELETE FROM tb_data_webform_submit WHERE tracker_id = ?");
	$stmt->bind_param("s", $tracker_id);
	$stmt->execute();
	$stmt->close();
}

?>