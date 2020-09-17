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

	if($_POST['action_type'] == "pull_mail_campaign_field_data")
		pullMailCampaignFieldData($conn);
	if($_POST['action_type'] == "save_campaign_list")
		saveCampaignList($conn);
	if($_POST['action_type'] == "get_campaign_list")
		getCampaignList($conn);
	if($_POST['action_type'] == "get_campaign_from_campaign_list_id")
		getCampaignFromCampaignListId($conn,$_POST['campaign_id']);
	if($_POST['action_type'] == "delete_campaign_from_campaign_id")
		deleteMailCampaignFromCampaignId($conn,$_POST['campaign_id']);
	if($_POST['action_type'] == "make_copy_campaign_list")
		makeCopyMailCampaignList($conn);
	if($_POST['action_type'] == "start_stop_mailCampaign")
		startStopMailCampaign($conn);


	if($_POST['action_type'] == "send_mail_direct")
		sendMailDirect($conn);

	if($_POST['action_type'] == "init_mail_campaign")
		InitMailCampaign($conn);
	if($_POST['action_type'] == "get_live_campaign_data")
		getLiveCampaignData($conn);
	if($_POST['action_type'] == "get_mail_replied")
		getMailReplied($conn);


	if($_POST['action_type'] == "multi_get_campaign_from_campaign_list_id__get_live_campaign_data")
		multi_get_campaign_from_campaign_list_id__get_live_campaign_data($conn);
}
else
    die();

//----------------------------------------------------------------------
function pullMailCampaignFieldData($conn){
	$resp;
	$result = mysqli_query($conn, "SELECT user_group_id,user_group_name FROM tb_core_mailcamp_user_group");
	if(mysqli_num_rows($result) > 0){
		$resp['user_group'] = mysqli_fetch_all($result, MYSQLI_ASSOC);
	}

	$result = mysqli_query($conn, "SELECT mail_template_id,mail_template_name FROM tb_core_mailcamp_template_list");
	if(mysqli_num_rows($result) > 0){
		$resp['mail_template'] = mysqli_fetch_all($result, MYSQLI_ASSOC);
	}

	$result = mysqli_query($conn, "SELECT sender_list_id,sender_name FROM tb_core_mailcamp_sender_list");
	if(mysqli_num_rows($result) > 0){
		$resp['mail_sender'] = mysqli_fetch_all($result, MYSQLI_ASSOC);
	}

	header('Content-Type: application/json');
	echo (json_encode($resp));
}

function saveCampaignList($conn){
	$campaign_id = $_POST['campaign_id'];
	if($campaign_id == '')
		$campaign_id = null;
	$mail_campaign_name = $_POST['mail_campaign_name'];
	$mail_campaign_user_group = base64_decode($_POST['mail_campaign_user_group']);
	$mail_campaign_mail_template = base64_decode($_POST['mail_campaign_mail_template']);
	$mail_campaign_mail_sender = base64_decode($_POST['mail_campaign_mail_sender']);
	$mail_campaign_scheduled_time = base64_decode($_POST['mail_campaign_scheduled_time']);
	$msg_interval = base64_decode($_POST['msg_interval']);
	$msg_fail_retry = $_POST['msg_fail_retry'];
	$camp_status = $_POST['camp_status'];

	if(checkCampaignListIdExist($conn,$campaign_id)){
		$stmt = $conn->prepare("UPDATE tb_core_mailcamp_list SET campaign_name=?, user_group=?, mail_template=?, mail_sender=?, scheduled_time=?, stop_time=null, msg_interval=?, msg_fail_retry=?, camp_status=?, camp_lock=0 WHERE campaign_id=?");
		$stmt->bind_param('sssssssss', $mail_campaign_name,$mail_campaign_user_group,$mail_campaign_mail_template,$mail_campaign_mail_sender,$mail_campaign_scheduled_time,$msg_interval,$msg_fail_retry,$camp_status,$campaign_id);
	}
	else{
		$stmt = $conn->prepare("INSERT INTO tb_core_mailcamp_list(campaign_id,campaign_name,user_group,mail_template,mail_sender,date,scheduled_time,msg_interval,msg_fail_retry,camp_status,camp_lock) VALUES(?,?,?,?,?,?,?,?,?,?,0)");
		$stmt->bind_param('ssssssssss', $campaign_id,$mail_campaign_name,$mail_campaign_user_group,$mail_campaign_mail_template,$mail_campaign_mail_sender,$GLOBALS['entry_time'],$mail_campaign_scheduled_time,$msg_interval,$msg_fail_retry,$camp_status);
	}
	
	if ($stmt->execute() === TRUE){
		deleteLiveMailcampData($conn,$campaign_id); /// Clear live data before starting or when campaign deletes
		kickStartCampaign($conn,$campaign_id);
		echo "success";
	}
	else
		echo "error";
}

function checkCampaignListIdExist($conn,$campaign_id){
	$stmt = $conn->prepare("SELECT camp_status FROM tb_core_mailcamp_list where campaign_id = ?");
	$stmt->bind_param("s", $campaign_id);
	$stmt->execute();
	if($row = $stmt->get_result()->fetch_assoc()){
		if($row['camp_status'] == 2 || $row['camp_status'] == 4)	//Cancel update operation update if 2-In Progress or 4-Mail sending only completed
			die('Error: campaign is running');
		else
			return true;
	}
	else
		return false;
}

function kickStartCampaign($conn,$campaign_id){
	$stmt = $conn->prepare("SELECT scheduled_time,camp_status FROM tb_core_mailcamp_list where campaign_id = ?");
	$stmt->bind_param("s", $campaign_id);
	$stmt->execute();
	$result = $stmt->get_result();
	if($row = $result->fetch_assoc()){
		if($row['camp_status'] == 1){//If scheduled
			$scheduled_time = date('d-m-Y h:i A',strtotime($row['scheduled_time']));
			$current_time = (new DateTime())->format('d-m-Y h:i A');
			if($scheduled_time >= $current_time)
				executeCron($conn,getOSType($conn),$campaign_id);
		}
	}
	$stmt->close();
	return false;
}

function getCampaignList($conn){
	header('Content-Type: application/json');
	$result = mysqli_query($conn, "SELECT campaign_id,campaign_name,user_group,mail_template,mail_sender,date,scheduled_time,stop_time,camp_status FROM tb_core_mailcamp_list");
	if(mysqli_num_rows($result) > 0)
		echo json_encode(mysqli_fetch_all($result, MYSQLI_ASSOC));
	else
		echo json_encode(['resp' => 'No data']);	
}

function getCampaignFromCampaignListId($conn,$campaign_id){
	$stmt = $conn->prepare("SELECT campaign_name,user_group,mail_template,mail_sender,date,scheduled_time,msg_interval,msg_fail_retry,camp_status FROM tb_core_mailcamp_list where campaign_id = ?");
	$stmt->bind_param("s", $campaign_id);
	$stmt->execute();
	$result = $stmt->get_result();
	if($row = $result->fetch_assoc()){
		header('Content-Type: application/json');
		echo json_encode($row) ;
	}
	else
		echo '{}';				
	$stmt->close();
}

function deleteMailCampaignFromCampaignId($conn,$campaign_id){	
	$stmt = $conn->prepare("DELETE FROM tb_core_mailcamp_list WHERE campaign_id = ?");
	$stmt->bind_param("s", $campaign_id);
	$stmt->execute();
	if($stmt->affected_rows != 0){
		echo "success";
		deleteLiveMailcampData($conn,$campaign_id); /// Clear live data before starting or when campaign deletes
	}
	else
		echo "error";
	$stmt->close();
}

function makeCopyMailCampaignList($conn){
	$old_campaign_id = $_POST['campaign_id'];
	$new_campaign_id = $_POST['new_campaign_id'];
	$new_campaign_name = $_POST['new_campaign_name'];

	$stmt = $conn->prepare("INSERT INTO tb_core_mailcamp_list (campaign_id,campaign_name,user_group,mail_template,mail_sender,date,scheduled_time,msg_interval,msg_fail_retry,camp_status) SELECT ?, ?, user_group,mail_template,mail_sender,?,scheduled_time,msg_interval,msg_fail_retry,0 FROM tb_core_mailcamp_list WHERE campaign_id=?");
	$stmt->bind_param("ssss", $new_campaign_id, $new_campaign_name, $GLOBALS['entry_time'], $old_campaign_id);
	
	if ($stmt->execute() === TRUE)
			die('success'); 
		else 
			die("failed"); 
	$stmt->close();
}

function startStopMailCampaign($conn){		
	$campaign_id = $_POST['campaign_id'];
	$action_value = $_POST['action_value'];
	if($action_value == 3)
		$stop_time = $GLOBALS['entry_time'];
	else
		$stop_time = null;

	$stmt = $conn->prepare("UPDATE tb_core_mailcamp_list SET camp_status=?,stop_time=? where campaign_id=?");
	$stmt->bind_param('sss', $action_value,$stop_time,$campaign_id);
	if ($stmt->execute() != TRUE)
		echo("failed"); 
	else
		echo("success"); 
	//------------------

	if($action_value == 1){	//if scheduled campaign
		deleteLiveMailcampData($conn,$campaign_id); // Clear live data before starting or when campaign deletes
		kickStartCampaign($conn,$campaign_id);
	}
}

function deleteLiveMailcampData($conn,$campaign_id){
	$stmt = $conn->prepare("DELETE FROM tb_data_mailcamp_live WHERE campaign_id = ?");
	$stmt->bind_param("s", $campaign_id);
	$stmt->execute();
	$stmt->close();
}

//====================================================================================================

function sendMailDirect($conn){

	$smtp_server_ip = explode(":", base64_decode($_POST['sender_list_mail_sender_SMTP_server']))[0];
	$smtp_server_port = explode(":", base64_decode($_POST['sender_list_mail_sender_SMTP_server']))[1];
	$sender_list_mail_sender_from_name = explode("<", base64_decode($_POST['sender_list_mail_sender_from']))[0];
	$sender_list_mail_sender_from_mail = str_replace(">","",explode("<", base64_decode($_POST['sender_list_mail_sender_from']))[1]);
	$sender_list_mail_sender_acc_username = base64_decode($_POST['sender_list_mail_sender_acc_username']);
	$sender_list_mail_sender_acc_pwd = base64_decode($_POST['sender_list_mail_sender_acc_pwd']);
	$sender_list_cust_headers = array_filter(explode("$#$",base64_decode($_POST['sender_list_cust_headers']))); //array_filter removes last empty array 
	$test_to_address = base64_decode($_POST['test_to_address']);

	$transport = (new Swift_SmtpTransport($smtp_server_ip, $smtp_server_port, 'ssl')) ->setUsername($sender_list_mail_sender_acc_username) ->setPassword($sender_list_mail_sender_acc_pwd);

	// Create the Mailer using your created Transport
	$mailer = new Swift_Mailer($transport);

	// Create a message
	$message = (new Swift_Message('SniperPhish Test Mail'))
  		->setFrom([$sender_list_mail_sender_from_mail => $sender_list_mail_sender_from_name])
  		->setTo([$test_to_address])
  		->setBody('Success. Here is the test message body');


	$headers = $message->getHeaders();	

	foreach ($sender_list_cust_headers as $header) {
		$header_name = trim(explode(":",$header)[0]);
		$header_val = trim(explode(":",$header)[1]);
		if ($headers->has($header_name)) {			// check if header exist
			if(strcasecmp($header_name, "return-path") == 0)
				$headers->get('Return-Path')->setAddress($header_val);
			else
    			$headers->get($header_name)->setValue($header_val);
    	}
    	else{
    		if(strcasecmp($header_name, "return-path") == 0)
				$headers->addPathHeader('Return-Path', $header_val);
			else
    			$headers->addTextHeader($header_name, $header_val);
    	}

	}
	//echo $headers->toString();

	try {
		// Send the message
		$result = $mailer->send($message);
		echo "success";
	} catch (Exception $e) {
  		echo $e->getMessage();
	}
    	
}

function getLiveCampaignData($conn){
	$stmt = $conn->prepare("SELECT * FROM tb_data_mailcamp_live where campaign_id = ?");
	$stmt->bind_param("s", $_POST['campaign_id']);
	$stmt->execute();
	$result = $stmt->get_result();
	if($result->num_rows > 0){
		header('Content-Type: application/json');
		echo json_encode(mysqli_fetch_all($result, MYSQLI_ASSOC));
	}	
	else
		echo json_encode(['resp' => 'No data']);	
	$stmt->close();	
}

function getMailReplied($conn){
	$sender_list_id = $_POST['sender_list_id'];	
	$user_group_id = $_POST['user_group_id'];
	$mail_template_id = $_POST['mail_template_id'];
	$reply_email = '';
	$arr_replied_mails = [];

	$stmt = $conn->prepare("SELECT sender_name,sender_SMTP_server,sender_from,sender_acc_username,sender_acc_pwd,sender_mailbox,cust_headers FROM tb_core_mailcamp_sender_list where sender_list_id = ?");
	$stmt->bind_param("s", $sender_list_id);
	$stmt->execute();
	$result = $stmt->get_result();
	if($result->num_rows > 0){
		$row = $result->fetch_assoc() ;
		$reply_email = str_replace(">","",explode("<",$row['sender_from'])[1]); //xxx <username@domain.com> => username@domain.com 
		$sender_acc_pwd = $row['sender_acc_pwd'];
		$sender_mailbox = $row['sender_mailbox'];
		$sender_list_cust_headers = explode("$#$",$row['cust_headers']);
		foreach($sender_list_cust_headers as $header){
		    $header_split = explode(':', $header);
		    if(strtoupper($header_split[0]) == 'REPLY-TO'){
		    	$header_split = $header_split[1];
		    	break;
		    }
		}

		//------------------Get mail subject---------
		$stmt = $conn->prepare("SELECT mail_template_subject FROM tb_core_mailcamp_template_list where mail_template_id = ?");
		$stmt->bind_param("s", $mail_template_id);
		$stmt->execute();
		$result = $stmt->get_result();
		if($row = $result->fetch_assoc())
			$mail_template_subject = $row['mail_template_subject'];
		else
			die("Unable to find email template");	

		//----------------------Get user emails----------
		$stmt = $conn->prepare("SELECT user_name,user_email,user_notes FROM tb_core_mailcamp_user_group where user_group_id = ?");
		$stmt->bind_param("s", $user_group_id);
		$stmt->execute();
		$result = $stmt->get_result();
		if($row = $result->fetch_assoc())
			$arr_emails =  array_filter(explode(",",$row['user_email']));
		else
			die("Unable to find email user email accounts in user group");		

		//-----------
		$arr_msg_info =[];
		$read = imap_open($sender_mailbox,$reply_email,$sender_acc_pwd) or die('error');
		 
		$array = imap_search($read,'SUBJECT "Re:" SUBJECT "'.$mail_template_subject.'"'); // search subject and "re:" in subject
		if($array) {
			foreach($array as $result) {
				$overview = imap_fetch_overview($read,$result,0);//var_dump($overview);
				$reply_mail_subject = $overview[0]->subject;

				$msg_from = strtolower(str_replace(">","",explode("<",$overview[0]->from)[1]));	//xxx <username@domain.com> => username@domain.com 
			    if(in_array($msg_from, array_map('strtolower', $arr_emails))){
			    	$msg_time = $overview[0]->date;			
			    	$msg_body = base64_encode((imap_fetchbody ($read,$result,1)));
			    	if (!array_key_exists($msg_from, $arr_msg_info))
					    $arr_msg_info[$msg_from] = ['msg_time'=>[$msg_time],'msg_body'=>[$msg_body]];
					else{
						array_push($arr_msg_info[$msg_from]['msg_time'],$msg_time);
						array_push($arr_msg_info[$msg_from]['msg_body'],$msg_body);
					}	
			    }
			}
		}	
		header('Content-Type: application/json');
		echo json_encode(['total_user_email_count'=>count($arr_emails), 'reply_count_unique'=>count($arr_msg_info), 'msg_info'=>$arr_msg_info]);
	}			
	$stmt->close();
}

function multi_get_campaign_from_campaign_list_id__get_live_campaign_data($conn){
	header('Content-Type: application/json');
	echo '{"campaign_data":';
	echo getCampaignFromCampaignListId($conn,$_POST['campaign_id']);
	echo ',"live_campaign_data":';
	echo getLiveCampaignData($conn);
	echo '}';
}

function getServerVariable($conn){
	$result = mysqli_query($conn, "SELECT win_uname,domain,win_pwd FROM tb_main_variables");
		if(mysqli_num_rows($result) > 0){
		return mysqli_fetch_all($result, MYSQLI_ASSOC)[0];
	}
}
?>