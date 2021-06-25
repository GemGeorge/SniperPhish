<?php
require_once(dirname(__FILE__) . '/session_manager.php');
if(isSessionValid() == false)
	die("Access denied");
//-------------------------------------------------------
date_default_timezone_set('UTC');
$entry_time = (new DateTime())->format('d-m-Y h:i A');
header('Content-Type: application/json');

if (isset($_POST)) {
	$POSTJ = json_decode(file_get_contents('php://input'),true);

	if(isset($POSTJ['action_type'])){
		if($POSTJ['action_type'] == "save_web_tracker")
			saveWebTracker($conn, $POSTJ);
	    if($POSTJ['action_type'] == "get_web_tracker_list")
			getWebTrackerList($conn);
	    if($POSTJ['action_type'] == "get_web_tracker_from_id")
			getWebTrackerFromId($conn, $POSTJ['tracker_id']);
	    if($POSTJ['action_type'] == "delete_web_tracker")
			deleteWebTracker($conn, $POSTJ['tracker_id']);
		if($POSTJ['action_type'] == "make_copy_web_tracker")
			makeCopyWebTracker($conn, $POSTJ['tracker_id'], $POSTJ['new_tracker_id'], $POSTJ['new_tracker_name']);
		if($POSTJ['action_type'] == "get_web_tracker_list_for_modal")
			getWebTrackerListForModal($conn);
		if($POSTJ['action_type'] == "pause_stop_web_tracker_tracking")
			pauseStopWebTrackerTracking($conn, $POSTJ['active'], $POSTJ['tracker_id'],false);
		if($POSTJ['action_type'] == "get_html_content")
			getHTMLContent($POSTJ['url']);
		if($POSTJ['action_type'] == "delete_web_tracker_data")
			deleteWebTrackerData($conn, $POSTJ['tracker_id']);

		if($POSTJ['action_type'] == "get_link_to_web_tracker")		//from mail template
			getLinktoWebTracker($conn);
	}
}

//-----------------------------
function saveWebTracker($conn, &$POSTJ) { 
	$tracker_step_data_string = base64_decode($POSTJ['tracker_step_data']);
	$tracker_step_data = json_decode($tracker_step_data_string,true);
	$tracker_code_output = json_decode(base64_decode($POSTJ['tracker_code_output']),true);
	$tracker_name = $tracker_step_data['start']['tb_tracker_name'];
	$active = $tracker_step_data['start']['cb_auto_ativate'] == true ? 1 : 0;
	$tracker_id = $POSTJ['tracker_id'];
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

	if ($stmt->execute() === TRUE){
		echo json_encode(['result' => 'success']);	
		pauseStopWebTrackerTracking($conn,$active,$tracker_id,true);
	}
	else 
		echo json_encode(['result' => 'failed', 'error' => 'Error saving data']);	
}

function getWebTrackerList($conn){	
	$result = mysqli_query($conn, "SELECT tracker_id,tracker_name,tracker_step_data,date,start_time,stop_time,active FROM tb_core_web_tracker_list");
	if(mysqli_num_rows($result) > 0)
		echo json_encode(mysqli_fetch_all($result, MYSQLI_ASSOC));
	else
		echo json_encode(['error' => 'No data']);
}

function getWebTrackerFromId($conn, $tracker_id){	
	$stmt = $conn->prepare("SELECT * FROM tb_core_web_tracker_list where tracker_id = ?");
	$stmt->bind_param("s", $tracker_id);
	$stmt->execute();
	$result = $stmt->get_result();
	if($result->num_rows != 0){
		$row = $result->fetch_assoc() ;
		echo json_encode($row);
	}
	else
		echo json_encode(['error' => 'No data']);				
	$stmt->close();
}

function deleteWebTracker($conn, $tracker_id){	
	$stmt = $conn->prepare("DELETE FROM tb_core_web_tracker_list WHERE tracker_id = ?");
	$stmt->bind_param("s", $tracker_id);
	$stmt->execute();
	if($stmt->affected_rows != 0)
		deleteWebTrackerData($conn,$tracker_id);
	else
		echo json_encode(['result' => 'failed', 'error' => 'Error deleting tracker!']);	
	$stmt->close();
}

function makeCopyWebTracker($conn, $old_tracker_id, $new_tracker_id, $new_tracker_name){
	$stmt = $conn->prepare("INSERT INTO tb_core_web_tracker_list (tracker_id,tracker_name,content_html,content_js,tracker_step_data,date,active) SELECT ?, ?, content_html,content_js,tracker_step_data,?,0 FROM tb_core_web_tracker_list WHERE tracker_id=?");
	$stmt->bind_param("ssss", $new_tracker_id, $new_tracker_name, $GLOBALS['entry_time'], $old_tracker_id);
	
	if($stmt->execute() === TRUE){
		echo(json_encode(['result' => 'success']));	
	}
	else 
		echo(json_encode(['result' => 'failed', 'error' => 'Error making copy!']));	
	$stmt->close();
}

function getWebTrackerListForModal($conn){	
	$result = mysqli_query($conn, "SELECT tracker_id,tracker_name,date FROM tb_core_web_tracker_list");
	if(mysqli_num_rows($result) > 0)
		echo json_encode(mysqli_fetch_all($result, MYSQLI_ASSOC),JSON_FORCE_OBJECT);
	else
		echo json_encode(['error' => 'No data']);	
}

function pauseStopWebTrackerTracking($conn,$active,$tracker_id,$quite){
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

	if($quite)
		$stmt->execute();
	else{
		if ($stmt->execute() === TRUE){
			echo(json_encode(['result' => 'success']));	
		}
		else 
			echo(json_encode(['result' => 'failed', 'error' => 'Error changing status']));	
		}	
}

function getHTMLContent($url){
	$ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:79.0) Gecko/20100101 Firefox/79.0');
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // ignore SSL errors
    $result=curl_exec($ch);
    curl_close($ch);
    if($result)
    	echo json_encode($result);
    else
    	echo json_encode(['result' => 'failed', 'error' => $stmt->error()]);
}

//-------------------------------------------------------------------------

function checkTrackerStartedPreviously($conn,$tracker_id){
	$stmt = $conn->prepare("SELECT start_time FROM tb_core_web_tracker_list WHERE tracker_id = ?");
	$stmt->bind_param("s", $tracker_id);
	$stmt->execute();
	$row = $stmt->get_result()->fetch_assoc();
	if($row['start_time'] == "")
		return false;
	else
		return true;
}

function checkWebTrackerIdExist($conn,$tracker_id){
	$stmt = $conn->prepare("SELECT COUNT(*) FROM tb_core_web_tracker_list WHERE tracker_id = ?");
	$stmt->bind_param("s", $tracker_id);
	$stmt->execute();
	$row = $stmt->get_result()->fetch_row();
	if($row[0] > 0)
		return true;
	else
		return false;
}

function deleteWebTrackerData($conn, $tracker_id){
	$stmt = $conn->prepare("DELETE FROM tb_data_webpage_visit WHERE tracker_id = ?");
	$stmt->bind_param("s", $tracker_id);
	$stmt->execute();

	$stmt = $conn->prepare("DELETE FROM tb_data_webform_submit WHERE tracker_id = ?");
	$stmt->bind_param("s", $tracker_id);
	$stmt->execute();
	$stmt->close();
	
	echo(json_encode(['result' => 'success']));	
}

//-----------------------------------------------------------------------------
function getLinktoWebTracker($conn){
	$resp = [];
	$result = mysqli_query($conn, "SELECT tracker_id,tracker_name,tracker_step_data FROM tb_core_web_tracker_list");
	if(mysqli_num_rows($result) > 0){
		while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
			$first_page = json_decode($row['tracker_step_data'],true)['web_forms']['data'][0]['page_url'];
		    array_push($resp, array('tracker_id' => $row['tracker_id'], 'tracker_name' => $row['tracker_name'], 'first_page' => $first_page));
		}
		echo json_encode($resp);
	}
	else
		echo json_encode(['error' => 'No data']);
}
?>