<?php
//-------------------Session check-----------------------
@ob_start();
session_start();
if(!isset($_SESSION['username']))
	die("Access denied");
//-------------------------------------------------------

require_once(dirname(__FILE__) . '/session_manager.php');
require '../vendor/autoload.php';
date_default_timezone_set('UTC');
$entry_time = (new DateTime())->format('d-m-Y h:i A');

if(isset($_POST['action_type'])){
	if($_POST['action_type'] == "get_campaign_list_web_mail")
		getCampaignListWebMail($conn);
	if($_POST['action_type'] == "multi_get_live_campaign_data_web_mail")
		multi_get_live_campaign_data_web_mail($conn);
}
else
    die();

//----------------------------------------------------------------------
function getCampaignListWebMail($conn){
	$result = mysqli_query($conn, "SELECT campaign_id,campaign_name,user_group,mail_template,mail_sender,date,scheduled_time,stop_time,camp_status FROM tb_core_mailcamp_list");
	if(mysqli_num_rows($result) > 0)
		$campaign['mailcamp_list'] = mysqli_fetch_all($result, MYSQLI_ASSOC);

	$result = mysqli_query($conn, "SELECT tracker_id,tracker_name,tracker_step_data,date,start_time,stop_time,active FROM tb_core_web_tracker_list");
	if(mysqli_num_rows($result) > 0)
		$campaign['webtracker_list'] = mysqli_fetch_all($result, MYSQLI_ASSOC);

	header('Content-Type: application/json');
	echo json_encode($campaign);
}

function multi_get_live_campaign_data_web_mail($conn){
	$campaign_id = $_POST['campaign_id'];
	$tracker_id = $_POST['tracker_id'];
	$user_group_id = $_POST['user_group_id'];
	$campaign['matched'] = [];
	$campaign['not_matched'] = [];

	$stmt = $conn->prepare("SELECT * FROM tb_data_mailcamp_live WHERE campaign_id = ?");
	$stmt->bind_param("s", $campaign_id);
	$stmt->execute();
	$result = $stmt->get_result();
	if($result->num_rows > 0)
		$campaign['mailcamp_live'] = mysqli_fetch_all($result, MYSQLI_ASSOC);
	else
		$campaign['mailcamp_live'] = [];	

	$stmt = $conn->prepare("SELECT * FROM tb_data_webpage_visit WHERE tracker_id = ?");
	$stmt->bind_param("s", $tracker_id);
	$stmt->execute();
	$result = $stmt->get_result();
	if($result->num_rows > 0)
		$campaign['webtracker_live']['page_visit'] = mysqli_fetch_all($result, MYSQLI_ASSOC);
	else
		$campaign['webtracker_live']['page_visit'] = [];

	$stmt = $conn->prepare("SELECT * FROM tb_data_webform_submit WHERE tracker_id = ?");
	$stmt->bind_param("s", $tracker_id);
	$stmt->execute();
	$result = $stmt->get_result();
	if($result->num_rows > 0)
		$campaign['webtracker_live']['form_submit'] = mysqli_fetch_all($result, MYSQLI_ASSOC);
	else
		$campaign['webtracker_live']['form_submit'] = [];	

	$stmt = $conn->prepare("SELECT * FROM tb_core_mailcamp_user_group where user_group_id = ?");
	$stmt->bind_param("s", $user_group_id);
	$stmt->execute();
	header('Content-Type: application/json');
	$result = $stmt->get_result();
	if($result->num_rows != 0)
		$user_group_data = $result->fetch_assoc() ;
	else
		$user_group_data = [];

	//----------------------------------
	$user_name_arr = array_filter(explode(",", $user_group_data['user_name']));  
	$user_email_arr = array_filter(explode(",", $user_group_data['user_email']));  
 	$user_email_name_arr = array_combine($user_email_arr, $user_name_arr);

 	$user_email_cid_arr=[];
 	foreach ($campaign['mailcamp_live'] as $item)
 		$user_email_cid_arr[$item['mailto_user_email']] = $item['id'];

 	$matched_page_visit = [];
 	$not_matched_page_visit = [];
 	foreach ($campaign['webtracker_live']['page_visit'] as $hit_entry){
 		$found_index =  array_search($hit_entry['cid'], array_column($campaign['mailcamp_live'], 'id')); //Get index that match tracked cid in the list of campaign users data id 
 		if(is_numeric($found_index)){	//index can be empty too
 			$email = $campaign['mailcamp_live'][$found_index]['mailto_user_email'];
 			if (!array_key_exists($email, $matched_page_visit))
			    $matched_page_visit[$email] = [];

			$hit_entry['mailto_user_name'] =  $campaign['mailcamp_live'][$found_index]['mailto_user_name'];
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
			$email = $campaign['mailcamp_live'][$found_index]['mailto_user_email'];
			if (!array_key_exists($email, $matched_form_submit))
				$matched_form_submit[$email]=[];
			if (!array_key_exists($hit_entry['page'], $matched_form_submit[$email]))
		    	$matched_form_submit[$email][$hit_entry['page']] = [];
		    $hit_entry['mailto_user_name'] =  $user_email_name_arr[$email];
			array_push($matched_form_submit[$email][$hit_entry['page']],$hit_entry);
		}
		else
			if(!in_array($hit_entry['id'],array_column($not_matched_form_submit, 'id')))
				array_push($not_matched_form_submit,$hit_entry);
	}

	$campaign['matched']['form_submission'] = $matched_form_submit;
 	$campaign['not_matched']['form_submission'] = $not_matched_form_submit;

 	header('Content-Type: application/json');
	echo json_encode($campaign);
	$stmt->close();
}
?>