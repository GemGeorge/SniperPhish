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
    if($_POST['action_type'] == "save_simple_tracker")
		saveSimpleTracker($conn);
    if($_POST['action_type'] == "get_simple_tracker_list")
		getSimpleTrackerList($conn);
    if($_POST['action_type'] == "delete_simple_tracker")
		deleteSimpleTracker($conn);
	if($_POST['action_type'] == "get_simple_tracker_from_id")
		getSimpleTrackerFromId($conn,$_POST['tracker_id']);
	if($_POST['action_type'] == "get_simple_tracker_data")
		getSimpleTrackerData($conn);
	if($_POST['action_type'] == "pause_stop_simple_tracker_tracking")
		pauseStopSimpleTrackerTracking($conn);
}
else
    die();

//-----------------------------
function pauseStopSimpleTrackerTracking($conn){	
	$active = $_POST['active'];
	$tracker_id = $_POST['tracker_id'];

	if($active == 0){ //stopping
		$stmt = $conn->prepare("UPDATE tb_core_simple_tracker_list SET active=?, stop_time=? where tracker_id=?");
		$stmt->bind_param('sss', $active,$GLOBALS['entry_time'],$tracker_id);
	}
	else
		if(trackerStartedPreviously($conn,$tracker_id) == true){
			$stmt = $conn->prepare("UPDATE tb_core_simple_tracker_list SET active=?  where tracker_id=?");
			$stmt->bind_param('ss', $active,$tracker_id);
		}
		else{
			$stmt = $conn->prepare("UPDATE tb_core_simple_tracker_list SET active=?, start_time=? where tracker_id=?");
			$stmt->bind_param('sss', $active,$GLOBALS['entry_time'],$tracker_id);
		}

	if ($stmt->execute() === TRUE)
		die('success'); 
	else 
		die("failed"); 	
}

function trackerStartedPreviously($conn,$tracker_id){
	$stmt = $conn->prepare("SELECT start_time FROM tb_core_simple_tracker_list where tracker_id = ?");
	$stmt->bind_param("s", $tracker_id);
	$stmt->execute();
	$row = $stmt->get_result()->fetch_assoc();
	
	if($row['start_time'] == "")
		return false;
	else
		return true;
}

function getSimpleTrackerData($conn){
	if(isset($_POST['tracker_id'])){
		$tracker_id = $_POST['tracker_id'];
	}
	else
		$tracker_id = 1;
	
	$stmt = $conn->prepare("SELECT * FROM tb_data_simple_tracker_live where tracker_id = ?");
	$stmt->bind_param("s", $tracker_id);
	$stmt->execute();
	$result = $stmt->get_result();
	header('Content-Type: application/json');
	if($result->num_rows > 0)
		echo json_encode(mysqli_fetch_all($result, MYSQLI_ASSOC));
	else
		echo json_encode(['resp' => 'No data']);
	$stmt->close();
}


function saveSimpleTracker($conn) { 

	$tracker_name = $_POST['simple_tracker_name'];
	$tracker_id = $_POST['tracker_id'];

	if(checkSimpleTrackerIdExist($conn,$tracker_id)){
		$stmt = $conn->prepare("UPDATE tb_core_simple_tracker_list SET tracker_name = ?, date =? WHERE tracker_id=?");
		$stmt->bind_param('sss', $tracker_name,$GLOBALS['entry_time'], $tracker_id);
	}
	else{
		$stmt = $conn->prepare("INSERT INTO tb_core_simple_tracker_list(tracker_id,tracker_name,date) VALUES(?,?,?)");
		$stmt->bind_param('sss', $tracker_id,$tracker_name,$GLOBALS['entry_time']);
	}
	if ($stmt->execute() === TRUE)
		die('success'); 
	else 
		die("failed"); 
}

function checkSimpleTrackerIdExist($conn,$tracker_id){
	$stmt = $conn->prepare("SELECT COUNT(*) FROM tb_core_simple_tracker_list WHERE tracker_id = ?");
	$stmt->bind_param("s", $tracker_id);
	$stmt->execute();
	$row = $stmt->get_result()->fetch_row();
	if($row[0] > 0)
		return true;
	else
		return false;
}

function getSimpleTrackerList($conn){	
	header('Content-Type: application/json');
	$result = mysqli_query($conn, "SELECT tracker_id,tracker_name,date,start_time,stop_time,active FROM tb_core_simple_tracker_list");
	if(mysqli_num_rows($result) > 0)
		echo json_encode(mysqli_fetch_all($result, MYSQLI_ASSOC));
	else
		echo json_encode(['resp' => 'No data']);	
}

function viewTemplate($conn){
	if(!isset($_POST['tracker_id']))
		die("Missing template id");
	$tracker_id = $_POST['tracker_id'];
	
	$stmt = $conn->prepare("SELECT * FROM tb_core_templates WHERE tracker_id = ?");
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

function getSimpleTrackerFromId($conn,$tracker_id){
	$stmt = $conn->prepare("SELECT * FROM tb_core_simple_tracker_list where tracker_id = ?");
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

function deleteSimpleTracker($conn){
	if(!isset($_POST['tracker_id']))
		die("Missing tracker id");
	$tracker_id = $_POST['tracker_id'];
	
	$stmt = $conn->prepare("DELETE FROM tb_core_simple_tracker_list WHERE tracker_id = ?");
	$stmt->bind_param("s", $tracker_id);
	$stmt->execute();
	//var_dump($stmt);
	if($stmt->affected_rows != 0)
		echo "deleted";
	else
		echo "error";
	$stmt->close();
	
}
?>