<?php
//-------------------Session check-----------------------
require_once(dirname(__FILE__) . '/session_manager.php');
if(isSessionValid() == false)
	die("Access denied");
//-------------------------------------------------------
header('Content-Type: application/json');

if (isset($_POST)) {
	$POSTJ = json_decode(file_get_contents('php://input'),true);

	if(isset($POSTJ['action_type'])){
		if($POSTJ['action_type'] == "get_home_graphs_data")
			getHomeGraphsData($conn);

		if($POSTJ['action_type'] == "check_process")
			checkSniperPhishProcess($conn,false);
		if($POSTJ['action_type'] == "start_process")
			startSniperPhishProcess($conn);
	}
}

//-----------------------------


function getHomeGraphsData($conn){
	$campaign_info = [];

	$stmt = $conn->prepare("SELECT tracker_id,tracker_name,date,start_time,stop_time,active FROM tb_core_web_tracker_list");
	$stmt->execute();
	$result = $stmt->get_result();
	if($result->num_rows > 0)
		$campaign_info['webtracker'] = mysqli_fetch_all($result, MYSQLI_ASSOC);
	else
		$campaign_info['webtracker'] =[];

	$stmt = $conn->prepare("SELECT campaign_id,campaign_name,date,scheduled_time,stop_time,camp_status FROM tb_core_mailcamp_list");
	$stmt->execute();
	$result = $stmt->get_result();
	if($result->num_rows > 0)
		$campaign_info['mailcamp'] = mysqli_fetch_all($result, MYSQLI_ASSOC);
	else
		$campaign_info['mailcamp'] = [];
	
	$stmt = $conn->prepare("SELECT tracker_id,tracker_name,date,start_time,stop_time,active FROM tb_core_quick_tracker_list");
	$stmt->execute();
	$result = $stmt->get_result();
	if($result->num_rows > 0)
		$campaign_info['quicktracker'] = mysqli_fetch_all($result, MYSQLI_ASSOC);
	else
		$campaign_info['quicktracker'] = [];

	echo json_encode($campaign_info);
	$stmt->close();
}

//-------------SniperPhish Process----------
function checkSniperPhishProcess($conn,$quite){
	if(isProcessRunning($conn,getOSType())){
		if($quite == false)
			echo json_encode(['result' => true]);
	    else
	    	return true;
	}
	else{
		if($quite == false)
	    	echo json_encode(['result' => false]);
	    else
	    	return false;
	}
}

function startSniperPhishProcess($conn){
	$os = getOSType();
	if(!isProcessRunning($conn,$os)){	//if process not running
		startProcess($os);
		
		sleep(1);	//wait for process start

		if(isProcessRunning($conn,$os))
			echo json_encode(['result' => true]);
		else			
	    	echo json_encode(['result' => false, 'error'=> 'Error starting service!']);
	}
	else
		echo json_encode(['result' => true]);	//Already running
}
?>