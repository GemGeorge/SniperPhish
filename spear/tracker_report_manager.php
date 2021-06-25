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
	    if($POSTJ['action_type'] == "get_table_webpage_visit_form_submission")
			getTableWebpageVisitFormSubmission($conn,  $POSTJ['tracker_id'], $POSTJ['page']);
		if($POSTJ['action_type'] == "get_web_tracker_from_id")
			getWebTrackerFromId($conn, $POSTJ['tracker_id']);
	}
}

//---------------------
function getTableWebpageVisitFormSubmission($conn, $tracker_id, $page){
	$resp = [];

	if($page == 0){
		$stmt = $conn->prepare("SELECT * FROM tb_data_webpage_visit WHERE tracker_id=?");
		$stmt->bind_param("s", $tracker_id);
	}
	else{
		$stmt = $conn->prepare("SELECT * FROM tb_data_webform_submit WHERE tracker_id=? AND page=?");
		$stmt->bind_param("ss", $tracker_id,$page);
	}
	
	$stmt->execute();
	$result = $stmt->get_result();
	$rows = $result->fetch_all(MYSQLI_ASSOC);
	foreach($rows as $i => $row){
		$row['ip_info'] = json_decode($row['ip_info']);
		if(!empty($row['form_field_data']))
			$row['form_field_data'] = json_decode($row['form_field_data']);
		array_push($resp,$row);
	}
	echo json_encode($resp);
	$stmt->close();
}

function getWebTrackerFromId($conn, $tracker_id){	
	$stmt = $conn->prepare("SELECT * FROM tb_core_web_tracker_list WHERE tracker_id = ?");
	$stmt->bind_param("s", $tracker_id);
	$stmt->execute();
	$result = $stmt->get_result();
	if($result->num_rows > 0){
		$row = $result->fetch_assoc();
		$row['tracker_step_data'] = json_decode($row["tracker_step_data"]);	
		echo json_encode($row) ;
	}
	else
		echo json_encode(['error' => 'No data']);
	$stmt->close();	
}
?>