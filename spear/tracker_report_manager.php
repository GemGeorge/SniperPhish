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
    if($_POST['action_type'] == "get_table_webpage_visit_form_submission")
		getTableWebpageVisitFormSubmission($conn);
    if($_POST['action_type'] == "table_form_submission_custom_fields")
		get_table_form_submission_custom_fields($conn);
	if($_POST['action_type'] == "get_web_tracker_from_id")
		getWebTrackerFromId($conn);
}
else
    die();


//---------------------
function get_table_form_submission_custom_fields($conn){
	if(isset($_POST['tracker_id'])){
	$tracker_id = $_POST['tracker_id'];
	}
	else
		$tracker_id = 1;
	
	$stmt = $conn->prepare("SELECT tracker_step4 FROM tb_core_web_tracker_list where tracker_id = ?");
	$stmt->bind_param("s", $tracker_id);
	$stmt->execute();
	$result = $stmt->get_result();
	
	if($result->num_rows > 0)
		$row = $result->fetch_assoc();
	else
		die("failed");	//Tracker Does Not Exist
	
	$cust_fields  = explode(";",$row['tracker_step4']);
	
	
	if($cust_fields[0] == "true"){
		foreach ($cust_fields as $key => $value) 
			if (substr($value, 0, 3) == 'FSB') 
				unset($cust_fields[$key]);
			
		array_shift($cust_fields); // removes 1st element
		try {	//Makes exception if element is only 1
			if($cust_fields[count($cust_fields)-1] == "")
				array_pop($cust_fields); // removes last empty element
		}catch (Exception $e) {}
		
		header('Content-Type: application/json');
		echo json_encode($cust_fields);
	}
	else
		echo "Disabled";
}




function endsWith($currentString, $target)
{
    $length = strlen($target);
    if ($length == 0) {
        return true;
    }
 
    return (substr($currentString, -$length) === $target);
}

function getTableWebpageVisitFormSubmission($conn){
	$tracker_id = $_POST['tracker_id'];
	$page = $_POST['page'];
	
	if($_POST['page'] == 0){
		$stmt = $conn->prepare("SELECT * FROM tb_data_webpage_visit WHERE tracker_id=?");
		$stmt->bind_param("s", $tracker_id);
	}
	else{
		$stmt = $conn->prepare("SELECT * FROM tb_data_webform_submit WHERE tracker_id=? AND page=?");
		$stmt->bind_param("ss", $tracker_id,$page);
	}
	
	$stmt->execute();
	$result = $stmt->get_result();	
	header('Content-Type: application/json');
	if($result->num_rows > 0)
		echo json_encode(mysqli_fetch_all($result, MYSQLI_ASSOC));
	else
		echo json_encode(['resp' => 'No data']);
	$stmt->close();
}

function getWebTrackerFromId($conn){
	if(!isset($_POST['tracker_id']))
		die("Missing tracker id");
	$tracker_id = $_POST['tracker_id'];
	
	$stmt = $conn->prepare("SELECT * FROM tb_core_web_tracker_list WHERE tracker_id = ?");
	$stmt->bind_param("s", $tracker_id);
	$stmt->execute();
	$result = $stmt->get_result();
	header('Content-Type: application/json');
	if($result->num_rows > 0)
		echo json_encode($result->fetch_assoc());
	else
		echo json_encode(['resp' => 'No data']);
	$stmt->close();	
}

?>