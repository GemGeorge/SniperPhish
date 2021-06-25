<?php
require_once(dirname(__FILE__) . '/session_manager.php');
require_once(dirname(__FILE__) . '/libs/swiftmailer/autoload.php');
//-------------------------------------------------------
date_default_timezone_set('UTC');
$entry_time = (new DateTime())->format('d-m-Y h:i A');
header('Content-Type: application/json');

if (isset($_POST)) {
	$POSTJ = json_decode(file_get_contents('php://input'),true);

	if(isset($POSTJ['action_type'])){

		if(isSessionValid() == false){
			$OPS = ['get_campaign_list_web_mail','multi_get_live_campaign_data_web_mail'];	//permited requests
			if(isset($POSTJ['tk_id']) && in_array($POSTJ['action_type'],$OPS)){
				if(isset($POSTJ['campaign_id']) && isset($POSTJ['tracker_id'])){
					if(!amIPublic($POSTJ['tk_id'],$POSTJ['campaign_id'], $POSTJ['tracker_id']))
						die("Access denied");
				}
				else
					if(isset($POSTJ['campaign_id'])){
						if(!amIPublic($POSTJ['tk_id'],$POSTJ['campaign_id']))
							die("Access denied");
					}
					else
						die("Access denied");
			}
			else
				die("Access denied");
		}

		if($POSTJ['action_type'] == "get_campaign_list_web_mail")
			getCampaignListWebMail($conn);
		if($POSTJ['action_type'] == "multi_get_live_campaign_data_web_mail")
			multi_get_live_campaign_data_web_mail($conn, $POSTJ['campaign_id'], $POSTJ['tracker_id']);
	}
}

//----------------------------------------------------------------------
function getCampaignListWebMail($conn){
	$resp = [];

	$result = mysqli_query($conn, "SELECT campaign_id,campaign_name,campaign_data,date,scheduled_time,camp_status FROM tb_core_mailcamp_list");
	if(mysqli_num_rows($result) > 0){
		foreach (mysqli_fetch_all($result, MYSQLI_ASSOC) as $row){
			$row["campaign_data"] = json_decode($row["campaign_data"]);	
        	array_push($resp,$row);
		}
		$campaign['mailcamp_list'] = $resp;
	}
	else
		$campaign['mailcamp_list'] = ['error' => 'No data'];


	$resp = [];

	$result = mysqli_query($conn, "SELECT tracker_id,tracker_name,tracker_step_data,date,start_time,stop_time,active FROM tb_core_web_tracker_list");
	if(mysqli_num_rows($result) > 0){
		foreach (mysqli_fetch_all($result, MYSQLI_ASSOC) as $row){
			$row["tracker_step_data"] = json_decode($row["tracker_step_data"]);	
        	array_push($resp,$row);
		}
		$campaign['webtracker_list'] = $resp;
	}
	else
		$campaign['webtracker_list'] = ['error' => 'No data'];

	echo json_encode($campaign);
}

function multi_get_live_campaign_data_web_mail($conn, $campaign_id, $tracker_id){
	$campaign['matched'] = [];
	$campaign['not_matched'] = [];
	$user_group_id = getCampaignDataFromCampaignID($conn, $campaign_id)['user_group']['id'];

	$campaign['mailcamp_live'] = [];
	$stmt = $conn->prepare("SELECT * FROM tb_data_mailcamp_live WHERE campaign_id = ?");
	$stmt->bind_param("s", $campaign_id);
	$stmt->execute();
	$result = $stmt->get_result();
	$rows = $result->fetch_all(MYSQLI_ASSOC);
	foreach($rows as $i => $row){
		$row['mail_open_times'] = json_decode($row['mail_open_times']);
		$row['public_ip'] = json_decode($row['public_ip']);
		$row['ip_info'] = json_decode($row['ip_info']);
		$row['user_agent'] = json_decode($row['user_agent']);
		$row['mail_client'] = json_decode($row['mail_client']);
		$row['platform'] = json_decode($row['platform']);
		$row['device_type'] = json_decode($row['device_type']);
		$row['all_headers'] = json_decode($row['all_headers']);
		array_push($campaign['mailcamp_live'],$row);
	}		

	$campaign['webtracker_live']['page_visit'] = [];
	$stmt = $conn->prepare("SELECT * FROM tb_data_webpage_visit WHERE tracker_id = ?");
	$stmt->bind_param("s", $tracker_id);
	$stmt->execute();
	$result = $stmt->get_result();
	$rows = $result->fetch_all(MYSQLI_ASSOC);
	foreach($rows as $i => $row){
		$row['ip_info'] = json_decode($row['ip_info'],true);		
		array_push($campaign['webtracker_live']['page_visit'],$row);
	}

	$campaign['webtracker_live']['form_submit'] =[];
	$stmt = $conn->prepare("SELECT * FROM tb_data_webform_submit WHERE tracker_id = ?");
	$stmt->bind_param("s", $tracker_id);
	$stmt->execute();
	$result = $stmt->get_result();
	$rows = $result->fetch_all(MYSQLI_ASSOC);
	foreach($rows as $i => $row){
		$row['ip_info'] = json_decode($row['ip_info']);		
		if(!empty($row['form_field_data']))
			$row['form_field_data'] = json_decode($row['form_field_data']);
		array_push($campaign['webtracker_live']['form_submit'],$row);
	}

	$stmt = $conn->prepare("SELECT * FROM tb_core_mailcamp_user_group WHERE user_group_id = ?");
	$stmt->bind_param("s", $user_group_id);
	$stmt->execute();
	$result = $stmt->get_result();
	if($result->num_rows != 0)
		$user_group_data = $result->fetch_assoc() ;
	else
		$user_group_data = [];

	//----------------------------------
	$user_name_arr=$user_email_arr=[];
	foreach(json_decode($user_group_data['user_data'],true) as $item){
		array_push($user_email_arr,$item['email']);
		array_push($user_name_arr,$item['name']);
	}
 	$user_email_name_arr = array_combine($user_email_arr, $user_name_arr);

 	$user_email_cid_arr=[];
 	foreach ($campaign['mailcamp_live'] as $item)
 		$user_email_cid_arr[$item['user_email']] = $item['id'];

 	$matched_page_visit = [];
 	$not_matched_page_visit = [];
 	foreach ($campaign['webtracker_live']['page_visit'] as $hit_entry){
 		$found_index =  array_search($hit_entry['cid'], array_column($campaign['mailcamp_live'], 'id')); //Get index that match tracked cid in the list of campaign users data id 
 		if(is_numeric($found_index)){	//index can be empty too
 			$email = $campaign['mailcamp_live'][$found_index]['user_email'];
 			if (!array_key_exists($email, $matched_page_visit))
			    $matched_page_visit[$email] = [];

			$hit_entry['user_name'] =  $campaign['mailcamp_live'][$found_index]['user_name'];
 			array_push($matched_page_visit[$email],$hit_entry);
 		}
 		else
 			array_push($not_matched_page_visit,$hit_entry);
 	}

 	$campaign['matched']['page_visit'] = $matched_page_visit;
 	$campaign['not_matched']['page_visit'] = $not_matched_page_visit;

 	$matched_form_submit = [];
 	$not_matched_form_submit = [];
	foreach ($campaign['webtracker_live']['form_submit'] as $hit_entry){
		$found_index =  array_search($hit_entry['cid'], array_column($campaign['mailcamp_live'], 'id')); //Get index that match tracked cid in the list of campaign users data id 
		if(is_numeric($found_index)){ //index can be empty too
			$email = $campaign['mailcamp_live'][$found_index]['user_email'];
			if (!array_key_exists($email, $matched_form_submit))
				$matched_form_submit[$email]=[];
			if (!array_key_exists($hit_entry['page'], $matched_form_submit[$email]))
		    	$matched_form_submit[$email][$hit_entry['page']] = [];
		    $hit_entry['user_name'] =  $user_email_name_arr[$email];
			array_push($matched_form_submit[$email][$hit_entry['page']],$hit_entry);
		}
		else
			if(!in_array($hit_entry['id'],array_column($not_matched_form_submit, 'id')))
				array_push($not_matched_form_submit,$hit_entry);
	}

	$campaign['matched']['form_submission'] = $matched_form_submit;
 	$campaign['not_matched']['form_submission'] = $not_matched_form_submit;

	echo json_encode($campaign);
	$stmt->close();
}
?>