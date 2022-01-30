<?php
require_once(dirname(__FILE__) . '/session_manager.php');
//-------------------------------------------------------
date_default_timezone_set('UTC');
$entry_time = (new DateTime())->format('d-m-Y h:i A');
header('Content-Type: application/json');

if (isset($_POST)) {
	$POSTJ = json_decode(file_get_contents('php://input'),true);

	if(isset($POSTJ['action_type'])){

		if(isSessionValid() == false){
			$OPS = ['multi_get_mcampinfo_from_mcamp_list_id_get_live_mcamp_data','get_user_group_data','get_mail_replied'];	//permited requests
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

		if($POSTJ['action_type'] == "save_campaign_list")
			saveCampaignList($conn, $POSTJ);
		if($POSTJ['action_type'] == "get_campaign_list")
			getCampaignList($conn);
		if($POSTJ['action_type'] == "get_campaign_from_campaign_list_id")
			getCampaignFromCampaignListId($conn,$POSTJ['campaign_id']);
		if($POSTJ['action_type'] == "delete_campaign_from_campaign_id")
			deleteMailCampaignFromCampaignId($conn,$POSTJ['campaign_id']);
		if($POSTJ['action_type'] == "make_copy_campaign_list")
			makeCopyMailCampaignList($conn,$POSTJ['campaign_id'], $POSTJ['new_campaign_id'], $POSTJ['new_campaign_name']);
		if($POSTJ['action_type'] == "pull_mail_campaign_field_data")
			pullMailCampaignFieldData($conn);
		if($POSTJ['action_type'] == "start_stop_mailCampaign")
			startStopMailCampaign($conn,$POSTJ['campaign_id'],$POSTJ['action_value']);		
			
		if($POSTJ['action_type'] == "get_user_group_data")
			getUserGroupData($conn,$POSTJ['campaign_id']);
		if($POSTJ['action_type'] == "get_mail_replied")
			getMailReplied($conn, $POSTJ['campaign_id']);	
		if($POSTJ['action_type'] == "multi_get_mcampinfo_from_mcamp_list_id_get_live_mcamp_data")
			multi_get_mcampinfo_from_mcamp_list_id_get_live_mcamp_data($conn,$POSTJ['campaign_id']);
	}
}
else
	die();

//----------------------------------------------------------------------
function saveCampaignList($conn, &$POSTJ){
	$campaign_id = $POSTJ['campaign_id'];
	$campaign_name = $POSTJ['campaign_name'];
	$campaign_data = json_encode($POSTJ['campaign_data']);
	$scheduled_time = $POSTJ['scheduled_time'];
	$camp_status = $POSTJ['camp_status'];

	if(checkCampaignListIdExist($conn,$campaign_id)){
		$stmt = $conn->prepare("UPDATE tb_core_mailcamp_list SET campaign_name=?, campaign_data=?, scheduled_time=?, stop_time=null, camp_status=?, camp_lock=0 WHERE campaign_id=?");
		$stmt->bind_param('sssss', $campaign_name,$campaign_data,$scheduled_time,$camp_status,$campaign_id);
	}
	else{
		$stmt = $conn->prepare("INSERT INTO tb_core_mailcamp_list(campaign_id,campaign_name,campaign_data,date,scheduled_time,camp_status,camp_lock) VALUES(?,?,?,?,?,?,0)");
		$stmt->bind_param('ssssss', $campaign_id,$campaign_name,$campaign_data,$GLOBALS['entry_time'],$scheduled_time,$camp_status);
	}
	

	if ($stmt->execute() === TRUE){
		deleteLiveMailcampData($conn,$campaign_id); /// Clear live data before starting or when campaign deletes
		kickStartCampaign($conn,$campaign_id);
		echo(json_encode(['result' => 'success']));	
	}
	else 
		echo(json_encode(['result' => 'failed', 'error' => $stmt->error]));	
}

function getCampaignList($conn){
	$resp = [];

	$result = mysqli_query($conn, "SELECT campaign_id,campaign_name,campaign_data,date,scheduled_time,stop_time,camp_status FROM tb_core_mailcamp_list");
	if(mysqli_num_rows($result) > 0){
		foreach (mysqli_fetch_all($result, MYSQLI_ASSOC) as $row){
			$row["campaign_data"] = json_decode($row["campaign_data"]);	//avoid double json encoding
        	array_push($resp,$row);
		}
		echo json_encode($resp);
	}
	else
		echo json_encode(['error' => 'No data']);	
}

function getCampaignFromCampaignListId($conn, $campaign_id,$quite=false){
	$stmt = $conn->prepare("SELECT campaign_name,campaign_data,date,scheduled_time,camp_status FROM tb_core_mailcamp_list WHERE campaign_id = ?");
	$stmt->bind_param("s", $campaign_id);
	$stmt->execute();
	$result = $stmt->get_result();
	if($row = $result->fetch_assoc()){
		$row["campaign_data"] = json_decode($row["campaign_data"]);	//avoid double json encoding
		if($quite)
			return $row;
		else
			echo json_encode($row) ;
	}
	else
		if($quite)
			return ['result' => 'No data'];
		else
			echo json_encode(['error' => 'No data']);	
	$stmt->close();
}

function deleteMailCampaignFromCampaignId($conn,$campaign_id){	
	$stmt = $conn->prepare("DELETE FROM tb_core_mailcamp_list WHERE campaign_id = ?");
	$stmt->bind_param("s", $campaign_id);
	$stmt->execute();
	if($stmt->affected_rows != 0){
		echo(json_encode(['result' => 'success']));	
		deleteLiveMailcampData($conn,$campaign_id); // Clear live data before starting or when campaign deletes
	}
	else
		echo(json_encode(['result' => 'failed', 'error' => $stmt->error]));	
	$stmt->close();
}

function makeCopyMailCampaignList($conn, $old_campaign_id, $new_campaign_id, $new_campaign_name){
	$stmt = $conn->prepare("INSERT INTO tb_core_mailcamp_list (campaign_id,campaign_name,campaign_data,date,scheduled_time,camp_status) SELECT ?, ?, campaign_data,?,scheduled_time,0 FROM tb_core_mailcamp_list WHERE campaign_id=?");
	$stmt->bind_param("ssss", $new_campaign_id, $new_campaign_name, $GLOBALS['entry_time'], $old_campaign_id);
	
	if ($stmt->execute() === TRUE){
		echo(json_encode(['result' => 'success']));	
	}
	else 
		echo(json_encode(['result' => 'failed', 'error' => $stmt->error]));	
	$stmt->close();
}

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

	$result = mysqli_query($conn, "SELECT mconfig_id,mconfig_name FROM tb_core_mailcamp_config");
	if(mysqli_num_rows($result) > 0){
		$resp['mail_config'] = mysqli_fetch_all($result, MYSQLI_ASSOC);
	}

	echo (json_encode($resp));
}

function startStopMailCampaign($conn, $campaign_id, $action_value){	
	if($action_value == 3)
		$stop_time = $GLOBALS['entry_time'];
	else
		$stop_time = null;

	$stmt = $conn->prepare("UPDATE tb_core_mailcamp_list SET camp_status=?,stop_time=? where campaign_id=?");
	$stmt->bind_param('sss', $action_value,$stop_time,$campaign_id);
	if ($stmt->execute() === TRUE){
		echo(json_encode(['result' => 'success']));	
	}
	else 
		echo(json_encode(['result' => 'failed', 'error' => $stmt->error]));	

	if($action_value == 1){	//if scheduled campaign
		deleteLiveMailcampData($conn,$campaign_id); // Clear live data before starting or when campaign deletes
		kickStartCampaign($conn,$campaign_id);
	}
}

function checkCampaignListIdExist($conn,$campaign_id){
	$stmt = $conn->prepare("SELECT camp_status FROM tb_core_mailcamp_list WHERE campaign_id = ?");
	$stmt->bind_param("s", $campaign_id);
	$stmt->execute();
	if($row = $stmt->get_result()->fetch_assoc()){
		if($row['camp_status'] == 2 || $row['camp_status'] == 4)	//Cancel update operation update if 2-In Progress or 4-Mail sending only completed
			die(json_encode(['result' => 'failed', 'error' => 'Error: campaign is running']));	
		else
			return true;
	}
	else
		return false;
}

function kickStartCampaign($conn,$campaign_id){
	$stmt = $conn->prepare("SELECT scheduled_time,camp_status FROM tb_core_mailcamp_list WHERE campaign_id = ?");
	$stmt->bind_param("s", $campaign_id);
	$stmt->execute();
	$result = $stmt->get_result();
	if($row = $result->fetch_assoc()){
		if($row['camp_status'] == 1){//If scheduled
			$scheduled_time = strtotime($row['scheduled_time']);
			$current_time = strtotime("now");
			if($scheduled_time <= $current_time)
				executeCron($conn,getOSType(),$campaign_id);
		}
	}
	$stmt->close();
	return false;
}

function deleteLiveMailcampData($conn,$campaign_id){
	$stmt = $conn->prepare("DELETE FROM tb_data_mailcamp_live WHERE campaign_id = ?");
	$stmt->bind_param("s", $campaign_id);
	$stmt->execute();
	$stmt->close();
}

//-----------------------------------------------------------------------------------------------------------
function getLiveCampaignData($conn, $campaign_id){
	$resp = [];

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
		array_push($resp,$row);
	}

	if(!empty($resp))
		return $resp;
	else
		return ['error' => 'No data'];	
}

function getUserGroupData($conn, $campaign_id){
	$campaign_data = getCampaignDataFromCampaignID($conn, $campaign_id);
	if(!empty($campaign_data)){
		$user_group_id = $campaign_data['user_group']['id'];
	
		$stmt = $conn->prepare("SELECT * FROM tb_core_mailcamp_user_group WHERE user_group_id = ?");
		$stmt->bind_param("s", $user_group_id);
		$stmt->execute();
		$result = $stmt->get_result();
		if($result->num_rows != 0){
			$row = $result->fetch_assoc();
			$row['user_data'] = json_decode($row["user_data"]);	//avoid double json encoding
			echo json_encode($row) ;
		}		
		else
			echo json_encode(['error' => 'No data']);	
	}
	else
		echo json_encode(['error' => 'No data']);	
	$stmt->close();
}

function getMailReplied($conn, $campaign_id){
	session_write_close(); //Required to avoid hanging by executing this fun
	$reply_email = '';
	$arr_replied_mails = [];
	$arr_err = [];

	$campaign_data = getCampaignDataFromCampaignID($conn, $campaign_id);
	$sender_list_id = $campaign_data['mail_sender']['id'];
	$user_group_id = $campaign_data['user_group']['id'];

	$stmt = $conn->prepare("SELECT sender_name,sender_SMTP_server,sender_from,sender_acc_username,sender_acc_pwd,sender_mailbox,cust_headers FROM tb_core_mailcamp_sender_list WHERE sender_list_id = ?");
	$stmt->bind_param("s", $sender_list_id);
	$stmt->execute();
	$result = $stmt->get_result();
	if($result->num_rows > 0){
		$row = $result->fetch_assoc() ;
		$reply_email = str_ireplace(">","",explode("<",$row['sender_from'])[1]); //xxx <username@domain.com> => username@domain.com 
		$sender_acc_pwd = $row['sender_acc_pwd'];
		$sender_mailbox = $row['sender_mailbox'];

		//------------------Get mail subject---------
		$stmt = $conn->prepare("SELECT id FROM tb_data_mailcamp_live WHERE campaign_id = ?");
		$stmt->bind_param("s", $campaign_id);
		$stmt->execute();
		$result = $stmt->get_result();
		$CIDs = [];
		while($row = $result->fetch_assoc())
			array_push($CIDs,$row['id']);
		
		//print_r($CIDs);

		//-----------
		$arr_msg_info =[];

		try{
			if($read = imap_open($sender_mailbox,$reply_email,$sender_acc_pwd)){			 
				$array = imap_search($read,'TEXT "@sniperphish.generated"'); // match for Message-ID header {{CID}}@sniperphish.generated
				foreach($array as $result) {
					$overview = imap_fetch_overview($read,$result,0); //var_dump($overview[0]->references);
					if($overview[0]->references == NULL)	
						$tmp = explode("@sniperphish.generated",$overview[0]->in_reply_to)[0]; //check reply mail header in_reply_to
					else
						$tmp = explode("@sniperphish.generated",$overview[0]->references)[0]; //check reply mail header references
					$header_to_check = explode("<",$tmp)[1];	//xxx {{CID}}@sniperphish.generated> => {{CID}} 

					//get email address part only
					if (filter_var($overview[0]->from, FILTER_VALIDATE_EMAIL))
	                    $msg_from = $overview[0]->from;
	                else
	                    $msg_from = str_ireplace(">","",explode("<",$overview[0]->from)[1]);	//xxx <username@domain.com> => username@domain.com 

				    if(in_array($header_to_check, $CIDs)){
				    	$msg_time = $overview[0]->date;			
				    	$msg_body = imap_fetchbody ($read,$result,1);
				    	if (!array_key_exists($msg_from, $arr_msg_info))
						    $arr_msg_info[$msg_from] = ['msg_time'=>[$msg_time],'msg_body'=>[$msg_body]];
						else{
							array_push($arr_msg_info[$msg_from]['msg_time'],$msg_time);
							array_push($arr_msg_info[$msg_from]['msg_body'],$msg_body);
						}	
				    }
				}
			}
		}catch(Exception $e) {
			array_push($arr_err,$e->getMessage());
		}
		array_push($arr_err,imap_errors());		//required to capture imap errors
		
		
		if(empty($arr_err) || $arr_err[0] == false)
			echo json_encode(['reply_count_unique'=>count($arr_msg_info), 'msg_info'=>$arr_msg_info]);
		else
			echo json_encode(['error'=>$arr_err, 'reply_count_unique'=>count($arr_msg_info), 'msg_info'=>$arr_msg_info]);
	}			
	$stmt->close();
}

function multi_get_mcampinfo_from_mcamp_list_id_get_live_mcamp_data($conn, $campaign_id){
	echo json_encode(['mcamp_info'=>getCampaignFromCampaignListId($conn,$campaign_id,true), 'live_mcamp_data'=>getLiveCampaignData($conn, $campaign_id,true)]);
}
?>