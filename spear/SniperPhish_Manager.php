<?php
ini_set('max_execution_time', 18000);
require_once(dirname(__FILE__) . '/db.php');
require_once(dirname(__FILE__) . '/common_functions.php');
date_default_timezone_set("UTC");
//---------------------------------------------------------

$os = getOSType($conn);

//Register cron, since cron not runnign already
$current_pid = getmypid();
$stmt = $conn->prepare("UPDATE tb_main_cron SET pid=?");
$stmt->bind_param('s', $current_pid);
$stmt->execute();

while(true){
	$camp_ids = getScheduledCampaigns($conn);
	foreach ($camp_ids as $campaign_id)
		executeCron($conn,$os,$campaign_id);
	sleep(5);
}


//--------------------------------------------------------------------------------------
function getScheduledCampaigns($conn){
	$camp_ids=[];
	$stmt = $conn->prepare("SELECT campaign_id,scheduled_time FROM tb_core_mailcamp_list WHERE camp_status=1 AND camp_lock=0");
	$stmt->execute();
	$result = $stmt->get_result();
	while($row = $result->fetch_assoc()){
		$scheduled_time_plus = date('d-m-Y h:i:s:u A',strtotime($row['scheduled_time'])-10);
		$current_time = (new DateTime())->format('d-m-Y h:i:s:u A');

		if($scheduled_time_plus < $current_time)
			array_push($camp_ids,$row['campaign_id']);
	}	
	$stmt->close();
	return $camp_ids;
}
?>