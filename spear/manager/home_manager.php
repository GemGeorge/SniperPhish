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
	$campaign_info= $timestamp_conv= $tmp=[];
	$campaign_info = ['webtracker'=>[], 'mailcamp'=>[], 'quicktracker'=>[]];
	$DTime_info = getTimeInfo($conn);

	$result = mysqli_query($conn, "SELECT tracker_id,tracker_name,date,start_time,stop_time,active FROM tb_core_web_tracker_list");
	if(mysqli_num_rows($result) > 0){
		foreach (mysqli_fetch_all($result, MYSQLI_ASSOC) as $row){
			$tmp['date']=$row['date']; $tmp['start_time']=$row['start_time']; $tmp['stop_time']=$row['stop_time']; 

			$row['date'] = getInClientTime_FD($DTime_info,$row['date']);
			$row['start_time'] = getInClientTime_FD($DTime_info,$row['start_time']);
			$row['stop_time'] = getInClientTime_FD($DTime_info,$row['stop_time']);

			$timestamp_conv[$row['date']] = getTimeInUnix(null,$tmp['date']);
			$timestamp_conv[$row['start_time']] = getTimeInUnix(null,$tmp['start_time']);
			$timestamp_conv[$row['stop_time']] = getTimeInUnix(null,$tmp['stop_time']);

        	array_push($campaign_info['webtracker'],$row);
		}
	}
	else
		$campaign_info['webtracker'] =[];

	$result = mysqli_query($conn, "SELECT campaign_id,campaign_name,date,scheduled_time,stop_time,camp_status FROM tb_core_mailcamp_list");
	if(mysqli_num_rows($result) > 0){
		foreach (mysqli_fetch_all($result, MYSQLI_ASSOC) as $row){
			$tmp['date']=$row['date']; $tmp['scheduled_time']=$row['scheduled_time']; $tmp['stop_time']=$row['stop_time'];

			$row['date'] = getInClientTime_FD($DTime_info,$row['date']);
			$row['scheduled_time'] = getInClientTime_FD($DTime_info,$row['scheduled_time']);
			$row['stop_time'] = getInClientTime_FD($DTime_info,$row['stop_time']);

			$timestamp_conv[$row['date']] = getTimeInUnix(null,$tmp['date']);
			$timestamp_conv[$row['scheduled_time']] = getTimeInUnix(null,$tmp['scheduled_time']);
			$timestamp_conv[$row['stop_time']] = getTimeInUnix(null,$tmp['stop_time']);

        	array_push($campaign_info['mailcamp'],$row);
		}
	}
	else
		$campaign_info['mailcamp'] = [];

	$result = mysqli_query($conn, "SELECT tracker_id,tracker_name,date,start_time,stop_time,active FROM tb_core_quick_tracker_list");
	if(mysqli_num_rows($result) > 0){
		foreach (mysqli_fetch_all($result, MYSQLI_ASSOC) as $row){
			$tmp['date']=$row['date']; $tmp['start_time']=$row['start_time']; $tmp['stop_time']=$row['stop_time']; 

			$row['date'] = getInClientTime_FD($DTime_info,$row['date']);
			$row['start_time'] = getInClientTime_FD($DTime_info,$row['start_time']);
			$row['stop_time'] = getInClientTime_FD($DTime_info,$row['stop_time']);

			$timestamp_conv[$row['date']] = getTimeInUnix(null,$tmp['date']);
			$timestamp_conv[$row['start_time']] = getTimeInUnix(null,$tmp['start_time']);
			$timestamp_conv[$row['stop_time']] = getTimeInUnix(null,$tmp['stop_time']);

        	array_push($campaign_info['quicktracker'],$row);
		}
	}
	else
		$campaign_info['quicktracker'] = [];

	echo json_encode(['campaign_info'=>$campaign_info, 'timestamp_conv'=>$timestamp_conv, 'timezone'=>$DTime_info['time_zone']['timezone']], JSON_INVALID_UTF8_IGNORE);
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