<?php
//-------------------Session check-----------------------
@ob_start();
session_start();
if(!isset($_SESSION['username']))
	die("Access denied");
//-------------------------------------------------------

require_once(dirname(__FILE__) . '/session_manager.php');

if(isset($_POST['action_type'])){
	if($_POST['action_type'] == "get_home_graphs_data")
		getHomeGraphsData($conn);

	if($_POST['action_type'] == "check_process")
		checkSniperPhishProcess($conn,false);
	if($_POST['action_type'] == "start_process")
		startSniperPhishProcess($conn);
}
else
    die();

//-----------------------------


function getHomeGraphsData($conn){
	$campaign_info = [];

	$stmt = $conn->prepare("SELECT tracker_id,tracker_name,date,start_time,stop_time,active FROM tb_core_web_tracker_list");
	$stmt->execute();
	$result = $stmt->get_result();
	if($result->num_rows > 0)
		$campaign_info['webtracker'] = mysqli_fetch_all($result, MYSQLI_ASSOC);

	$stmt = $conn->prepare("SELECT campaign_id,campaign_name,date,scheduled_time,stop_time,camp_status FROM tb_core_mailcamp_list");
	$stmt->execute();
	$result = $stmt->get_result();
	if($result->num_rows > 0)
		$campaign_info['mailcamp'] = mysqli_fetch_all($result, MYSQLI_ASSOC);
	

	$stmt = $conn->prepare("SELECT tracker_id,tracker_name,date,start_time,stop_time,active FROM tb_core_simple_tracker_list");
	$stmt->execute();
	$result = $stmt->get_result();
	if($result->num_rows > 0)
		$campaign_info['simpletracker'] = mysqli_fetch_all($result, MYSQLI_ASSOC);

	header('Content-Type: application/json');
	echo json_encode($campaign_info);
	$stmt->close();
}

//-------------SniperPhish Process----------
function checkSniperPhishProcess($conn,$quite){
	if(isProcessRunning($conn,getOSType($conn))){
		if($quite == false)
	    	echo ("success");
	    else
	    	return true;
	}
	else{
		if($quite == false)
	    	echo ("failed");
	    else
	    	return false;
	}
}

function startSniperPhishProcess($conn){
	$os = getOSType($conn);
	if(!isProcessRunning($conn,$os)){	//if process not running
		startProcess($os);
		
		sleep(1);	//wait for process start

		if(isProcessRunning($conn,$os))
			die("success");
		else
			die("failed");
	}
	die("success");	//Already running
}
?>