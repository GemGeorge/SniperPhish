<?php
require_once(dirname(__FILE__) . '/session_manager.php');
require_once(dirname(__FILE__) . '/common_functions.php');

if(isSessionValid() == false)
	die("Access denied");
//-------------------------------------------------------
date_default_timezone_set('UTC');
$entry_time = (new DateTime())->format('d-m-Y h:i A');
header('Content-Type: application/json');

if (isset($_POST)) {
	$POSTJ = json_decode(file_get_contents('php://input'),true);

	if(isset($POSTJ['action_type'])){
		if($POSTJ['action_type'] == "save_quick_tracker")
			saveQuickTracker($conn,$POSTJ);
		if($POSTJ['action_type'] == "get_quick_tracker_list")
			getQuickTrackerList($conn);
		if($POSTJ['action_type'] == "delete_quick_tracker")
			deleteQuickTracker($conn, $POSTJ['tracker_id']);
		if($POSTJ['action_type'] == "delete_quick_tracker_data")
			deleteQuickTrackerData($conn, $POSTJ['tracker_id']);
		if($POSTJ['action_type'] == "pause_stop_quick_tracker_tracking")
			pauseStopQuickTrackerTracking($conn, $POSTJ['tracker_id'], $POSTJ['active']);
	    
		if($POSTJ['action_type'] == "get_quick_tracker_from_id")
			getQuickTrackerFromId($conn,$POSTJ['tracker_id']);
		if($POSTJ['action_type'] == "get_quick_tracker_data")
			getQuickTrackerData($conn,$POSTJ['tracker_id']);
	}
}

//-----------------------------
function saveQuickTracker($conn, &$POSTJ) { 
	$tracker_name = $POSTJ['quick_tracker_name'];
	$tracker_id = $POSTJ['tracker_id'];

	if(checkQuickTrackerIdExist($conn,$tracker_id)){
		$stmt = $conn->prepare("UPDATE tb_core_quick_tracker_list SET tracker_name = ?, date =? WHERE tracker_id=?");
		$stmt->bind_param('sss', $tracker_name,$GLOBALS['entry_time'], $tracker_id);
	}
	else{
		$stmt = $conn->prepare("INSERT INTO tb_core_quick_tracker_list(tracker_id,tracker_name,date) VALUES(?,?,?)");
		$stmt->bind_param('sss', $tracker_id,$tracker_name,$GLOBALS['entry_time']);
	}
	if ($stmt->execute() === TRUE)
		echo json_encode(['result' => 'success']);	
	else 
		echo json_encode(['result' => 'failed', 'error' => 'Error saving data']);	
}

function getQuickTrackerList($conn){
	$result = mysqli_query($conn, "SELECT tracker_id,tracker_name,date,start_time,stop_time,active FROM tb_core_quick_tracker_list");
	if(mysqli_num_rows($result) > 0)
		echo json_encode(mysqli_fetch_all($result, MYSQLI_ASSOC));
	else
		echo json_encode(['error' => 'No data']);	
}

function deleteQuickTracker($conn, $tracker_id){	
	$stmt = $conn->prepare("DELETE FROM tb_core_quick_tracker_list WHERE tracker_id = ?");
	$stmt->bind_param("s", $tracker_id);
	$stmt->execute();

	if($stmt->affected_rows != 0)
		deleteQuickTrackerData($conn, $tracker_id);
	else
		echo json_encode(['result' => 'failed', 'error' => 'Quick tracker id does not exist']);	
	$stmt->close();	
}

function pauseStopQuickTrackerTracking($conn, $tracker_id, $active){	
	if($active == 0){ //stopping
		$stmt = $conn->prepare("UPDATE tb_core_quick_tracker_list SET active=?, stop_time=? WHERE tracker_id=?");
		$stmt->bind_param('sss', $active,$GLOBALS['entry_time'],$tracker_id);
	}
	else
		if(trackerStartedPreviously($conn,$tracker_id) == true){
			$stmt = $conn->prepare("UPDATE tb_core_quick_tracker_list SET active=?  WHERE tracker_id=?");
			$stmt->bind_param('ss', $active,$tracker_id);
		}
		else{
			$stmt = $conn->prepare("UPDATE tb_core_quick_tracker_list SET active=?, start_time=? WHERE tracker_id=?");
			$stmt->bind_param('sss', $active,$GLOBALS['entry_time'],$tracker_id);
		}

	if ($stmt->execute() === TRUE){
		echo(json_encode(['result' => 'success']));	
	}
	else 
		echo(json_encode(['result' => 'failed', 'error' => 'Error changing status']));	
}

function deleteQuickTrackerData($conn, $tracker_id){
	$stmt = $conn->prepare("DELETE FROM tb_data_quick_tracker_live WHERE tracker_id = ?");
	$stmt->bind_param("s", $tracker_id);
	
	if ($stmt->execute() === TRUE)
		echo(json_encode(['result' => 'success']));	
	else 
		echo(json_encode(['result' => 'failed', 'error' => 'Error deleting Quick tracker']));	
}

//---------------------------Start report section----------------
function getQuickTrackerFromId($conn,$tracker_id){
	$stmt = $conn->prepare("SELECT * FROM tb_core_quick_tracker_list WHERE tracker_id = ?");
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

function getQuickTrackerData($conn, $tracker_id){	
	$resp = [];
	$stmt = $conn->prepare("SELECT * FROM tb_data_quick_tracker_live WHERE tracker_id = ?");
	$stmt->bind_param("s", $tracker_id);
	$stmt->execute();
	$result = $stmt->get_result();
	$rows = $result->fetch_all(MYSQLI_ASSOC);
	foreach($rows as $i => $row){
		$row['ip_info'] = json_decode($row['ip_info']);
		array_push($resp,$row);
	}
	echo json_encode($resp);
	$stmt->close();
}

//---------------------------End  report section----------------
function trackerStartedPreviously($conn,$tracker_id){
	$stmt = $conn->prepare("SELECT start_time FROM tb_core_quick_tracker_list WHERE tracker_id = ?");
	$stmt->bind_param("s", $tracker_id);
	$stmt->execute();
	$row = $stmt->get_result()->fetch_assoc();
	
	if($row['start_time'] == "")
		return false;
	else
		return true;
}

function checkQuickTrackerIdExist($conn,$tracker_id){
	$stmt = $conn->prepare("SELECT COUNT(*) FROM tb_core_quick_tracker_list WHERE tracker_id = ?");
	$stmt->bind_param("s", $tracker_id);
	$stmt->execute();
	$row = $stmt->get_result()->fetch_row();
	if($row[0] > 0)
		return true;
	else
		return false;
}

?>