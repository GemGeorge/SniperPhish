<?php
require_once(dirname(__FILE__) . '/session_manager.php');
require_once(dirname(__FILE__) . '/common_functions.php');
require_once(dirname(__FILE__) . '/libs/swiftmailer/autoload.php');
require_once(dirname(__FILE__) . '/libs/qr_barcode/qrcode.php');
require_once(dirname(__FILE__) . '/libs/qr_barcode/barcode.php');
if(isSessionValid() == false)
	die("Access denied");
//-------------------------------------------------------
date_default_timezone_set('UTC');
$entry_time = (new DateTime())->format('d-m-Y h:i A');
header('Content-Type: application/json');

if (isset($_POST)) {
	$POSTJ = json_decode(file_get_contents('php://input'),true);

	if(isset($POSTJ['action_type'])){
		if($POSTJ['action_type'] == "save_user_group")
			saveUserGroup($conn, $POSTJ);
		if($POSTJ['action_type'] == "get_user_group_list")
			getUserGroupList($conn);
		if($POSTJ['action_type'] == "get_user_group_from_group_Id")
			getUserGroupFromGroupId($conn,$POSTJ['user_group_id']);
		if($POSTJ['action_type'] == "delete_user_group_from_group_id")
			deleteUserGroupFromGroupId($conn,$POSTJ['user_group_id']);
		if($POSTJ['action_type'] == "make_copy_user_group")
			makeCopyUserGroup($conn, $POSTJ['user_group_id'], $POSTJ['new_user_group_id'], $POSTJ['new_user_group_name']);

		if($POSTJ['action_type'] == "save_mail_template")
			saveMailTemplate($conn,$POSTJ);
		if($POSTJ['action_type'] == "get_mail_template_list")
			getMailTemplateList($conn);
		if($POSTJ['action_type'] == "get_mail_template_from_template_id")
			getMailTemplateFromTemplateId($conn,$POSTJ['mail_template_id']);
		if($POSTJ['action_type'] == "delete_mail_template_from_template_id")
			deleteMailTemplateFromTemplateId($conn,$POSTJ['mail_template_id']);
		if($POSTJ['action_type'] == "make_copy_mail_template")
			makeCopyMailTemplate($conn, $POSTJ['mail_template_id'], $POSTJ['new_mail_template_id'], $POSTJ['new_mail_template_name']);
		if($POSTJ['action_type'] == "upload_tracker_image")
			uploadTrackerImage($conn,$POSTJ);
		if($POSTJ['action_type'] == "upload_attachments")
			uploadAttachment($conn,$POSTJ);
		if($POSTJ['action_type'] == "upload_mail_body_files")
			uploadMailBodyFiles($conn,$POSTJ);

		if($POSTJ['action_type'] == "save_sender_list")
			saveSenderList($conn, $POSTJ);
		if($POSTJ['action_type'] == "get_sender_list")
			getSenderList($conn);	
		if($POSTJ['action_type'] == "get_sender_from_sender_list_id")
			getSenderFromSenderListId($conn,$POSTJ['sender_list_id']);	
		if($POSTJ['action_type'] == "delete_mail_sender_list_from_list_id")
			deleteMailSenderListFromSenderId($conn,$POSTJ['sender_list_id']);
		if($POSTJ['action_type'] == "make_copy_sender_list")
			makeCopyMailSenderList($conn,$POSTJ['sender_list_id'],$POSTJ['new_sender_list_id'],$POSTJ['new_sender_list_name']);
		if($POSTJ['action_type'] == "verify_mailbox_access")
			verifyMailboxAccess($conn,$POSTJ);

		if($POSTJ['action_type'] == "send_test_mail_verification")
			sendTestMailVerification($conn,$POSTJ);
		if($POSTJ['action_type'] == "send_test_mail_sample")
			sendTestMailSample($conn,$POSTJ);
	}
}

//-----------------------------

function saveUserGroup($conn, &$POSTJ){
	$user_group_id = $POSTJ['user_group_id'];
	$user_group_name = $POSTJ['user_group_name'];
	$user_data = json_encode($POSTJ['user_data']);

	if(checkUserGroupIdExist($conn,$user_group_id)){
		$stmt = $conn->prepare("UPDATE tb_core_mailcamp_user_group SET user_group_name=?, user_data=? WHERE user_group_id=?");
		$stmt->bind_param('sss', $user_group_name,$user_data,$user_group_id);
	}
	else{
		$stmt = $conn->prepare("INSERT INTO tb_core_mailcamp_user_group(user_group_id,user_group_name,user_data,date) VALUES(?,?,?,?)");
		$stmt->bind_param('ssss', $user_group_id,$user_group_name,$user_data,$GLOBALS['entry_time']);
	}
	
	if ($stmt->execute() === TRUE)
		echo(json_encode(['result' => 'success']));	
	else 
		echo(json_encode(['result' => 'failed', 'error' => 'Error saving data!']));	
}

function getUserGroupList($conn){
	$resp = [];
	$result = mysqli_query($conn, "SELECT * FROM tb_core_mailcamp_user_group");
	if(mysqli_num_rows($result) > 0){
		foreach (mysqli_fetch_all($result, MYSQLI_ASSOC) as $row){
			$row["user_data"] = json_decode($row["user_data"]);	//avoid double json encoding
        	array_push($resp,$row);
		}
		echo json_encode($resp,JSON_FORCE_OBJECT);
	}
	else
		echo json_encode(['error' => 'No data']);	
}

function getUserGroupFromGroupId($conn,$user_group_id){
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
	$stmt->close();
}

function deleteUserGroupFromGroupId($conn,$user_group_id){	
	$stmt = $conn->prepare("DELETE FROM tb_core_mailcamp_user_group WHERE user_group_id = ?");
	$stmt->bind_param("s", $user_group_id);
	$stmt->execute();
	if($stmt->affected_rows != 0)
		echo json_encode(['result' => 'success']);	
	else
		echo json_encode(['result' => 'failed', 'error' => 'User group does not exist']);	
	$stmt->close();
}

function makeCopyUserGroup($conn, $old_user_group_id, $new_user_group_id, $new_user_group_name){
	$stmt = $conn->prepare("INSERT INTO tb_core_mailcamp_user_group (user_group_id,user_group_name,user_data,date) SELECT ?, ?,user_data,? FROM tb_core_mailcamp_user_group WHERE user_group_id=?");
	$stmt->bind_param("ssss", $new_user_group_id, $new_user_group_name, $GLOBALS['entry_time'], $old_user_group_id);
	
	if($stmt->execute() === TRUE){
		echo(json_encode(['result' => 'success']));	
	}
	else 
		echo(json_encode(['result' => 'failed', 'error' => 'Error making copy!']));	
	$stmt->close();
}

function checkUserGroupIdExist($conn,$user_group_id){
	$stmt = $conn->prepare("SELECT COUNT(*) FROM tb_core_mailcamp_user_group WHERE user_group_id = ?");
	$stmt->bind_param("s", $user_group_id);
	$stmt->execute();
	$row = $stmt->get_result()->fetch_row();
	if($row[0] > 0)
		return true;
	else
		return false;
}
//---------------------------------------Email Template Section --------------------------------

function saveMailTemplate($conn,&$POSTJ){
	$mail_template_id = $POSTJ['mail_template_id'];
	if($mail_template_id == '')
		$mail_template_id = null;

	$mail_template_name = $POSTJ['mail_template_name'];
	$mail_template_subject = $POSTJ['mail_template_subject'];
	$mail_template_content = $POSTJ['mail_template_content'];
	$timage_type = $POSTJ['timage_type'];
	$attachments = json_encode($POSTJ['attachments']);
	$mail_content_type = $POSTJ['mail_content_type'];

	if(checkMailTemplateIdExist($conn,$mail_template_id)){
		$stmt = $conn->prepare("UPDATE tb_core_mailcamp_template_list SET mail_template_name=?, mail_template_subject=?, mail_template_content=?, timage_type=?, mail_content_type=?, attachment=? WHERE mail_template_id=?");
		$stmt->bind_param('sssssss', $mail_template_name,$mail_template_subject, $mail_template_content,$timage_type,$mail_content_type,$attachments,$mail_template_id);
	}
	else{
		$stmt = $conn->prepare("INSERT INTO tb_core_mailcamp_template_list(mail_template_id, mail_template_name, mail_template_subject, mail_template_content, timage_type, mail_content_type, attachment, date) VALUES(?,?,?,?,?,?,?,?)");
		$stmt->bind_param('ssssssss', $mail_template_id,$mail_template_name,$mail_template_subject,$mail_template_content,$timage_type,$mail_content_type,$attachments,$GLOBALS['entry_time']);
	}
	
	if ($stmt->execute() === TRUE){
		echo(json_encode(['result' => 'success']));	
	}
	else 
		echo(json_encode(['result' => 'failed', 'error' => $stmt->error]));	
}

function getMailTemplateList($conn){
	$resp = [];
	$result = mysqli_query($conn, "SELECT mail_template_id, mail_template_name, LEFT(mail_template_subject , 50) mail_template_subject, LEFT(mail_template_content , 50) mail_template_content,attachment,date FROM tb_core_mailcamp_template_list");

	if(mysqli_num_rows($result) > 0){
		foreach (mysqli_fetch_all($result, MYSQLI_ASSOC) as $row){
			$row["attachment"] = json_decode($row["attachment"]);	//avoid double json encoding
        	array_push($resp,$row);
		}
		echo json_encode($resp,JSON_FORCE_OBJECT);
	}
	else
		echo json_encode(['error' => 'No data']);	
	$result->close();
}

function getMailTemplateFromTemplateId($conn, $mail_template_id){
	$stmt = $conn->prepare("SELECT * FROM tb_core_mailcamp_template_list WHERE mail_template_id = ?");
	$stmt->bind_param("s", $mail_template_id);
	$stmt->execute();
	$result = $stmt->get_result();
	if($result->num_rows != 0){
		$row = $result->fetch_assoc() ;
		$row['attachment'] = json_decode($row['attachment']);
		echo json_encode($row) ;
	}
	else
		echo json_encode(['error' => 'No data']);				
	$stmt->close();
}

function deleteMailTemplateFromTemplateId($conn,$mail_template_id){	
	$stmt = $conn->prepare("DELETE FROM tb_core_mailcamp_template_list WHERE mail_template_id = ?");
	$stmt->bind_param("s", $mail_template_id);
	$stmt->execute();
	if($stmt->affected_rows != 0)
		echo json_encode(['result' => 'success']);	
	else
		echo json_encode(['result' => 'failed', 'error' => 'Mail template does not exist']);	
	$stmt->close();
}

function makeCopyMailTemplate($conn, $old_mail_template_id, $new_mail_template_id, $new_mail_template_name){
	$stmt = $conn->prepare("INSERT INTO tb_core_mailcamp_template_list (mail_template_id,mail_template_name,mail_template_subject,mail_template_content,timage_type,mail_content_type,attachment,date) SELECT ?, ?, mail_template_subject,mail_template_content,timage_type,mail_content_type,attachment,? FROM tb_core_mailcamp_template_list WHERE mail_template_id=?");
	$stmt->bind_param("ssss", $new_mail_template_id, $new_mail_template_name, $GLOBALS['entry_time'], $old_mail_template_id);
	
	if ($stmt->execute() === TRUE)
		echo json_encode(['result' => 'success']);	
	else
		echo json_encode(['result' => 'failed', 'error' => $stmt->error]);	
	$stmt->close();
}

function uploadTrackerImage($conn,&$POSTJ){
	$mail_template_id = $POSTJ['mail_template_id'];
	$file_name = filter_var($POSTJ['file_name'], FILTER_SANITIZE_STRING);
	$file_b64 = explode(',', $POSTJ['file_b64'])[1];
	$binary_data = base64_decode($file_b64);

	$target_file = 'uploads/timages/'.$mail_template_id.'.timg';
	if(getimagesizefromstring($binary_data)){
        try{
        	file_put_contents($target_file,$binary_data);
        	echo(json_encode(['result' => 'success']));	
        }catch(Exception $e) {
			echo(json_encode(['result' => 'failed', 'error' => $e->getMessage()]));	
		}        	
    }
    else
    	echo(json_encode(['result' => 'failed', 'error' => 'Invalid file']));	
}

function uploadAttachment($conn,&$POSTJ){
	$mail_template_id = $POSTJ['mail_template_id'];
	$file_name = filter_var($POSTJ['file_name'], FILTER_SANITIZE_STRING);
	$file_b64 = explode(',', $POSTJ['file_b64'])[1];
	$binary_data = base64_decode($file_b64);
	$file_id = $mail_template_id.'_'.time();

	$target_file = 'uploads/attachments/'.$file_id.'.att';

	if (!is_dir('uploads/attachments/')) 
		die(json_encode(['result' => 'failed', 'error' => 'Directory uploads/attachments/ does not exist']));
	if (!is_writable('uploads/attachments/')) 
		die(json_encode(['result' => 'failed', 'error' => 'Directory uploads/attachments/ has no write permission']));

	try{
    	if(file_put_contents($target_file,$binary_data) || file_exists($target_file))	//if 0 size file failed, check if they exist (written)
    		echo(json_encode(['result' => 'success', 'file_id' => $file_id]));	
    	else
			echo(json_encode(['result' => 'failed', 'error' => 'File upload failed!']));	
    }catch(Exception $e) {
		echo(json_encode(['result' => 'failed', 'error' => $e->getMessage()]));	
	}       
}

function uploadMailBodyFiles($conn,&$POSTJ){
	$mail_template_id = $POSTJ['mail_template_id'];
	$file_name = filter_var($POSTJ['file_name'], FILTER_SANITIZE_STRING);
	$file_b64 = explode(',', $POSTJ['file_b64'])[1];
	$binary_data = base64_decode($file_b64);
	$file_id_part = time();
	$file_id = $mail_template_id.'_'.$file_id_part;

	$target_file = 'uploads/attachments/'.$file_id.'.mbf';

	if (!is_dir('uploads/attachments/')) 
		die(json_encode(['result' => 'failed', 'error' => 'Directory uploads/attachments/ does not exist']));
	if (!is_writable('uploads/attachments/')) 
		die(json_encode(['result' => 'failed', 'error' => 'Directory uploads/attachments/ has no write permission']));

	try{
    	if(file_put_contents($target_file,$binary_data) || file_exists($target_file))	//if 0 size file failed, check if they exist (written)
    		echo(json_encode(['result' => 'success', 'file_id' => $file_id, "mbf" => $file_id_part]));	
    	else
    		echo(json_encode(['result' => 'failed', 'error' => $e->getMessage()]));	
    }catch(Exception $e) {
		echo(json_encode(['result' => 'failed', 'error' =>'File upload failed!']));	
	}       
}

function checkMailTemplateIdExist($conn,$mail_template_id){
	$stmt = $conn->prepare("SELECT COUNT(*) FROM tb_core_mailcamp_template_list WHERE mail_template_id = ?");
	$stmt->bind_param("s", $mail_template_id);
	$stmt->execute();
	$row = $stmt->get_result()->fetch_row();
	if($row[0] > 0)
		return true;
	else
		return false;
}
//---------------------------------------Sender List Section --------------------------------
function saveSenderList($conn, &$POSTJ){
	$sender_list_id = $POSTJ['sender_list_id'];
	$sender_list_mail_sender_name = $POSTJ['sender_list_mail_sender_name'];
	$sender_list_mail_sender_SMTP_server = $POSTJ['sender_list_mail_sender_SMTP_server'];
	$sender_list_mail_sender_from = $POSTJ['sender_list_mail_sender_from'];
	$sender_list_mail_sender_acc_username = $POSTJ['sender_list_mail_sender_acc_username'];
	$sender_list_mail_sender_acc_pwd = $POSTJ['sender_list_mail_sender_acc_pwd'];
	$smtp_enc_level = $POSTJ['range_SMTP_enc_level'];
	$auto_mailbox = $POSTJ['cb_auto_mailbox'];
	$mail_sender_mailbox = $POSTJ['mail_sender_mailbox'];
	$sender_list_cust_headers = json_encode($POSTJ['sender_list_cust_headers']); 

	if(checkSenderListIdExist($conn,$sender_list_id)){
		if($sender_list_mail_sender_acc_pwd != ''){	//new sender acc pwd
			$stmt = $conn->prepare("UPDATE tb_core_mailcamp_sender_list SET sender_name=?, sender_SMTP_server=?, sender_from=?, sender_acc_username=?, sender_acc_pwd=?, smtp_enc_level=?, auto_mailbox=?, sender_mailbox=?, cust_headers=? WHERE sender_list_id=?");
			$stmt->bind_param('ssssssssss', $sender_list_mail_sender_name,$sender_list_mail_sender_SMTP_server,$sender_list_mail_sender_from,$sender_list_mail_sender_acc_username,$sender_list_mail_sender_acc_pwd,$smtp_enc_level,$auto_mailbox,$mail_sender_mailbox,$sender_list_cust_headers,$sender_list_id);
		}
		else{	//sender acc pwd has no change
			$stmt = $conn->prepare("UPDATE tb_core_mailcamp_sender_list SET sender_name=?, sender_SMTP_server=?, sender_from=?, sender_acc_username=?, smtp_enc_level=?, auto_mailbox=?, sender_mailbox=?, cust_headers=? WHERE sender_list_id=?");
			$stmt->bind_param('sssssssss', $sender_list_mail_sender_name,$sender_list_mail_sender_SMTP_server,$sender_list_mail_sender_from,$sender_list_mail_sender_acc_username,$smtp_enc_level,$auto_mailbox,$mail_sender_mailbox,$sender_list_cust_headers,$sender_list_id);
		}
	}
	else{
		$stmt = $conn->prepare("INSERT INTO tb_core_mailcamp_sender_list(sender_list_id,sender_name,sender_SMTP_server,sender_from,sender_acc_username,sender_acc_pwd,smtp_enc_level,auto_mailbox,sender_mailbox,cust_headers,date) VALUES(?,?,?,?,?,?,?,?,?,?,?)");
		$stmt->bind_param('sssssssssss', $sender_list_id,$sender_list_mail_sender_name,$sender_list_mail_sender_SMTP_server,$sender_list_mail_sender_from,$sender_list_mail_sender_acc_username,$sender_list_mail_sender_acc_pwd,$smtp_enc_level,$auto_mailbox,$mail_sender_mailbox,$sender_list_cust_headers,$GLOBALS['entry_time']);
	}
	
	if ($stmt->execute() === TRUE)
		echo json_encode(['result' => 'success']);
	else 
		echo json_encode(['result' => 'failed']);
}

function getSenderList($conn){
	$resp = [];

	$result = mysqli_query($conn, "SELECT sender_list_id,sender_name,sender_SMTP_server,sender_from,sender_acc_username,smtp_enc_level,sender_mailbox,cust_headers,date FROM tb_core_mailcamp_sender_list");
	if(mysqli_num_rows($result) > 0){
		foreach (mysqli_fetch_all($result, MYSQLI_ASSOC) as $row){
			$row["cust_headers"] = json_decode($row["cust_headers"]);	//avoid double json encoding
        	array_push($resp,$row);
		}
		echo json_encode($resp,JSON_FORCE_OBJECT);
	}
	else
		echo json_encode(['error' => 'No data']);	
	$result->close();
}

function getSenderFromSenderListId($conn, $sender_list_id){
	$stmt = $conn->prepare("SELECT sender_name,sender_SMTP_server,sender_from,sender_acc_username,smtp_enc_level,auto_mailbox,sender_mailbox,cust_headers FROM tb_core_mailcamp_sender_list WHERE sender_list_id = ?");
	$stmt->bind_param("s", $sender_list_id);
	$stmt->execute();
	$result = $stmt->get_result();
	if($result->num_rows > 0){
		$row = $result->fetch_assoc() ;
		$row["cust_headers"] = json_decode($row["cust_headers"]);	//avoid double json encoding
		echo json_encode($row) ;
	}			
	else
		echo json_encode(['error' => 'No data']);	
	$stmt->close();
}

function deleteMailSenderListFromSenderId($conn, $sender_list_id){	
	$stmt = $conn->prepare("DELETE FROM tb_core_mailcamp_sender_list WHERE sender_list_id = ?");
	$stmt->bind_param("s", $sender_list_id);
	$stmt->execute();
	if($stmt->affected_rows != 0)
		echo json_encode(['result' => 'success']);	
	else
		echo json_encode(['result' => 'failed', 'error' => 'Error deleting sender!']);	
	$stmt->close();
}

function makeCopyMailSenderList($conn, $old_sender_list_id, $new_sender_list_id, $new_sender_list_name){
	$stmt = $conn->prepare("INSERT INTO tb_core_mailcamp_sender_list (sender_list_id,sender_name,sender_SMTP_server,sender_from,sender_acc_username,sender_acc_pwd,smtp_enc_level,auto_mailbox,sender_mailbox,cust_headers,date) SELECT ?, ?, sender_SMTP_server,sender_from,sender_acc_username,sender_acc_pwd,smtp_enc_level,auto_mailbox,sender_mailbox,cust_headers,? FROM tb_core_mailcamp_sender_list WHERE sender_list_id=?");
	$stmt->bind_param("ssss", $new_sender_list_id, $new_sender_list_name, $GLOBALS['entry_time'], $old_sender_list_id);
	
	if ($stmt->execute() === TRUE)
		echo json_encode(['result' => 'success']);	
	else
		echo json_encode(['result' => 'failed', 'error' => $stmt->error]);	
	$stmt->close();
}

function verifyMailboxAccess($conn, $POSTJ){
	$sender_list_id = $POSTJ['sender_list_id'];
	$sender_username = $POSTJ['mail_sender_acc_username'];
	$sender_pwd = $POSTJ['mail_sender_acc_pwd'];
	$sender_mailbox = $POSTJ['mail_sender_mailbox'];

	if(empty($sender_pwd))
		$sender_pwd = getSenderPwd($conn, $sender_list_id);

	if(empty($sender_pwd))
		die(json_encode(['result' => 'failed', 'error' => "Sender list does not exist. Please fill the password field"]));	
	else{
		try{
			$imap_obj = imap_open($sender_mailbox,$sender_username,$sender_pwd);		
	    	$resp = ['result' => 'success', 'total_msg_count' => imap_num_msg($imap_obj)];
		} catch (Exception $e) {
	  		$resp = ['result' => 'failed', 'error' =>$e->getMessage()];
		}

		$imap_err = imap_errors(); //required to capture imap errors
		if(!empty($imap_err))
			$resp = ['result' => 'failed', 'error' => $imap_err];	
	}	

	echo json_encode($resp);
}

function checkSenderListIdExist($conn,$sender_list_id){
	$stmt = $conn->prepare("SELECT COUNT(*) FROM tb_core_mailcamp_sender_list WHERE sender_list_id = ?");
	$stmt->bind_param("s", $sender_list_id);
	$stmt->execute();
	$row = $stmt->get_result()->fetch_row();
	if($row[0] > 0)
		return true;
	else
		return false;
}
//---------------------------------------End Sender List Section --------------------------------
//====================================================================================================
function sendTestMailVerification($conn,$POSTJ){
	$sender_list_id = $POSTJ['sender_list_id'];
	$smtp_server = $POSTJ['sender_list_mail_sender_SMTP_server'];
	$sender_from = $POSTJ['sender_list_mail_sender_from'];
	$sender_username = $POSTJ['sender_list_mail_sender_acc_username'];
	$sender_pwd = $POSTJ['sender_list_mail_sender_acc_pwd'];
	$cust_headers = $POSTJ['sender_list_cust_headers'];
	$test_to_address = $POSTJ['test_to_address'];
	$smtp_enc_level = $POSTJ['range_SMTP_enc_level'];
	$mail_subject = "SniperPhish Test Mail";
	$mail_body = "Success. Here is the test message body";
	$mail_content_type = "text/plain";
	$message = new Swift_Message();

	//-----------------------------------
	if(empty($sender_pwd))
		$sender_pwd = getSenderPwd($conn, $sender_list_id);

	if(empty($sender_pwd))
		die(json_encode(['result' => 'failed', 'error' => "Sender list does not exist. Please fill the password field"]));	
	else
		shootMail($message,$smtp_server,$sender_username,$sender_pwd,$sender_from,$test_to_address,$smtp_enc_level,$cust_headers,$mail_subject,$mail_body,$mail_content_type);
}

function sendTestMailSample($conn,$POSTJ){
	$sender_list_id = $POSTJ['sender_list_id'];
	$smtp_server = $POSTJ['smtp_server'];
	$sender_from = $POSTJ['sender_from'];
	$sender_username = $POSTJ['sender_username'];
	$sender_pwd = $POSTJ['sender_pwd'];
	$cust_headers = $POSTJ['cust_headers'];
	$test_to_address = $POSTJ['test_to_address'];
	$smtp_enc_level = $POSTJ['smtp_enc_level'];
	$mail_subject = $POSTJ['mail_subject'];
	$mail_body = $POSTJ['mail_body'];
	$mail_content_type = $POSTJ['mail_content_type'];
	$mail_attachment = $POSTJ['attachments'];


	$keyword_vals = array();
	$serv_variables = getServerVariable($conn);
	$CID = getRandomStr(10);

    $keyword_vals['{{CID}}'] = $CID;
    $keyword_vals['{{MID}}'] = "MailCampaign_id";
    $keyword_vals['{{NAME}}'] = "ABC XYZ";
    $keyword_vals['{{FNAME}}'] = "ABC";
    $keyword_vals['{{LNAME}}'] = "XYZ";
    $keyword_vals['{{NOTES}}'] = "Note_content";
    $keyword_vals['{{EMAIL}}'] = $test_to_address;
    $keyword_vals['{{FROM}}'] = $sender_from;
    $keyword_vals['{{TRACKINGURL}}'] = $serv_variables['baseurl'].'/tmail?mid='."MailCampaign_id".'&cid='.$CID;
    $keyword_vals['{{TRACKER}}'] = '<img src="'.$keyword_vals['{{TRACKINGURL}}'].'"/>';
    $keyword_vals['{{BASEURL}}'] = $serv_variables['baseurl'];
	$keyword_vals['{{MUSERNAME}}'] = explode('@', $test_to_address)[0];
	$keyword_vals['{{MDOMAIN}}'] = explode('@', $test_to_address)[1];

    $message = new Swift_Message();
	$mail_subject = filterKeywords($mail_subject,$keyword_vals);
	$mail_body = filterKeywords($mail_body,$keyword_vals);  	
	$mail_body = filterQRBarCode($mail_body,$keyword_vals,$message);

	foreach ($mail_attachment as $attachment) {
		$file_path = 'uploads/attachments/'.$attachment['file_id'].'.att';
		$file_disp_name = filterKeywords($attachment['file_disp_name'],$keyword_vals);

		if($attachment['inline'])
	    	$message->attach(Swift_Attachment::fromPath($file_path,mime_content_type($file_path))->setFilename($file_disp_name)->setDisposition('inline'));
	    else
	    	$message->attach(Swift_Attachment::fromPath($file_path,mime_content_type($file_path))->setFilename($file_disp_name));
	}

	//-----------------------------------
	if(empty($sender_pwd)){
		$stmt = $conn->prepare("SELECT sender_acc_pwd FROM tb_core_mailcamp_sender_list WHERE sender_list_id = ?");
		$stmt->bind_param("s", $sender_list_id);
		$stmt->execute();
		$result = $stmt->get_result();
		if($row = $result->fetch_assoc())
			$sender_pwd = $row['sender_acc_pwd'];
		else
			die(json_encode(['result' => 'failed', 'error' => "Sender list does not exist. Please fill the password field"]));	
	}
	//---------------------------
	shootMail($message,$smtp_server,$sender_username,$sender_pwd,$sender_from,$test_to_address,$smtp_enc_level,$cust_headers,$mail_subject,$mail_body,$mail_content_type);  
}
//===================================================================================================
function getSenderPwd(&$conn, &$sender_list_id){
	$stmt = $conn->prepare("SELECT sender_acc_pwd FROM tb_core_mailcamp_sender_list WHERE sender_list_id = ?");
	$stmt->bind_param("s", $sender_list_id);
	$stmt->execute();
	$result = $stmt->get_result();
	if($row = $result->fetch_assoc())
		return $row['sender_acc_pwd'];
	else
		return "";
}
?>