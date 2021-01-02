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
    if($_POST['action_type'] == "save_user_group")
		saveUserGroup($conn);
	if($_POST['action_type'] == "get_user_group_from_group_Id")
		getUserGroupFromGroupId($conn,$_POST['user_group_id']);
	if($_POST['action_type'] == "get_user_group_list")
		getUserGroupList($conn);
	if($_POST['action_type'] == "delete_user_group_from_group_id")
		deleteUserGroupFromGroupId($conn,$_POST['user_group_id']);
	if($_POST['action_type'] == "make_copy_user_group")
		makeCopyUserGroup($conn);
	if($_POST['action_type'] == "verify_mailbox_access")
		verifyMailboxAccess($conn);

	if($_POST['action_type'] == "save_mail_template")
		saveMailTemplate($conn);
	if($_POST['action_type'] == "get_mail_template_from_template_id")
		getMailTemplateFromTemplateId($conn,$_POST['mail_template_id']);
    if($_POST['action_type'] == "get_mail_template_list")
		getMailTemplateList($conn);
	if($_POST['action_type'] == "delete_mail_template_from_template_id")
		deleteMailTemplateFromTemplateId($conn,$_POST['mail_template_id']);
	if($_POST['action_type'] == "make_copy_mail_template")
		makeCopyMailTemplate($conn);
	if($_POST['action_type'] == "upload_tracker_image")
		uploadTrackerImage($conn);
	if($_POST['action_type'] == "remove_tracker_image")
		removeTrackerImage($conn,false);
	if($_POST['action_type'] == "upload_attachment")
		uploadAttachment($conn);
	if($_POST['action_type'] == "delete_attachment")
		deleteAttachment($conn);

	if($_POST['action_type'] == "save_sender_list")
		saveSenderList($conn);
	if($_POST['action_type'] == "get_sender_list")
		getSenderList($conn);
	if($_POST['action_type'] == "get_sender_from_sender_list_id")
		getSenderFromSenderListId($conn,$_POST['sender_list_id']);
	if($_POST['action_type'] == "delete_mail_sender_list_from_list_id")
		deleteMailSenderListFromSenderId($conn,$_POST['sender_list_id']);
	if($_POST['action_type'] == "make_copy_sender_list")
		makeCopyMailSenderList($conn);
}
else
    die();

//-----------------------------


function saveUserGroup($conn){
	$user_group_id = $_POST['user_group_id'];
	if($user_group_id == '')
		$user_group_id = null;
	$user_group_name = $_POST['user_group_name'];
	$user_name = base64_decode($_POST['user_name']);
	$user_email = base64_decode($_POST['user_email']);
	$user_notes = base64_decode($_POST['user_notes']);

	if(checkUserGroupIdExist($conn,$user_group_id)){
		$stmt = $conn->prepare("UPDATE tb_core_mailcamp_user_group SET user_group_name=?, user_name=?, user_email=?, user_notes=? WHERE user_group_id=?");
		$stmt->bind_param('sssss', $user_group_name,$user_name,$user_email,$user_notes,$user_group_id);
	}
	else{
		$stmt = $conn->prepare("INSERT INTO tb_core_mailcamp_user_group(user_group_id,user_group_name,user_name,user_email,user_notes,date) VALUES(?,?,?,?,?,?)");
		$stmt->bind_param('ssssss', $user_group_id,$user_group_name,$user_name,$user_email,$user_notes,$GLOBALS['entry_time']);
	}
	
	if ($stmt->execute() === TRUE)
		die('success'); 
	else 
		die("failed"); 
}

function checkUserGroupIdExist($conn,$user_group_id){
	$stmt = $conn->prepare("SELECT COUNT(*) FROM tb_core_mailcamp_user_group where user_group_id = ?");
	$stmt->bind_param("s", $user_group_id);
	$stmt->execute();
	$row = $stmt->get_result()->fetch_row();
	if($row[0] > 0)
		return true;
	else
		return false;
}

function getUserGroupFromGroupId($conn,$user_group_id){
	$stmt = $conn->prepare("SELECT * FROM tb_core_mailcamp_user_group where user_group_id = ?");
	$stmt->bind_param("s", $user_group_id);
	$stmt->execute();
	header('Content-Type: application/json');
	$result = $stmt->get_result();
	if($result->num_rows != 0){
		$row = $result->fetch_assoc() ;
		echo json_encode($row) ;
	}			
	$stmt->close();
}


function getUserGroupList($conn){
	$myArray = [];
    header('Content-Type: application/json');
	if ($result = mysqli_query($conn, "SELECT user_group_id,user_group_name,user_email,date FROM tb_core_mailcamp_user_group")) {
    	while($row = $result->fetch_array(MYSQLI_ASSOC)) {
    		$row['user_email'] = count(explode(",",$row['user_email'])); //replaces emails with its count
            $myArray[] = $row;
    	}
    	if($myArray)
    		echo json_encode($myArray);
    	else
			echo json_encode(['resp' => 'No data']);
	}

	$result->close();
}

function deleteUserGroupFromGroupId($conn,$user_group_id){	
	$stmt = $conn->prepare("DELETE FROM tb_core_mailcamp_user_group WHERE user_group_id = ?");
	$stmt->bind_param("s", $user_group_id);
	$stmt->execute();
	if($stmt->affected_rows != 0)
		echo "deleted";
	else
		echo "error";
	$stmt->close();
}

function makeCopyUserGroup($conn){
	$old_user_group_id = $_POST['user_group_id'];
	$new_user_group_id = $_POST['new_user_group_id'];
	$new_user_group_name = $_POST['new_user_group_name'];

	$stmt = $conn->prepare("INSERT INTO tb_core_mailcamp_user_group (user_group_id,user_group_name,user_name,user_email,user_notes,date) SELECT ?, ?,user_name,user_email,user_notes,? FROM tb_core_mailcamp_user_group WHERE user_group_id=?");
	$stmt->bind_param("ssss", $new_user_group_id, $new_user_group_name, $GLOBALS['entry_time'], $old_user_group_id);
	
	if ($stmt->execute() === TRUE)
			die('success'); 
		else 
			die("failed"); 
	$stmt->close();
}

function verifyMailboxAccess($conn){
	$sender_acc_username = base64_decode($_POST['mail_sender_acc_username']);
	$sender_acc_pwd = base64_decode($_POST['mail_sender_acc_pwd']);
	$mail_sender_mailbox = base64_decode($_POST['mail_sender_mailbox']);
	$arr_err = [];	

	try{
		$read = imap_open($mail_sender_mailbox,$sender_acc_username,$sender_acc_pwd);

	} catch (Exception $e) {
  		array_push($arr_err,$e->getMessage());
	}
	array_push($arr_err,imap_errors());		//required to capture imap errors

	header('Content-Type: application/json');
	echo json_encode(['error'=>$arr_err]);
}
//---------------------------------------Email Template Section --------------------------------

function saveMailTemplate($conn){
	$mail_template_id = $_POST['mail_template_id'];
	if($mail_template_id == '')
		$mail_template_id = null;


	$mail_template_name = $_POST['mail_template_name'];
	$mail_template_subject = base64_decode($_POST['mail_template_subject']);
	$mail_template_content = base64_decode($_POST['mail_template_content']);
	$cust_timage = $_POST['cust_timage'];
	$attachment = base64_decode($_POST['attachment']);
	$mail_content_type = $_POST['mail_content_type'];

	if(checkMailTemplateIdExist($conn,$mail_template_id)){
		$stmt = $conn->prepare("UPDATE tb_core_mailcamp_template_list SET mail_template_name=?, mail_template_subject=?, mail_template_content=?, cust_timage=?, mail_content_type=?, attachment=? WHERE mail_template_id=?");
		$stmt->bind_param('sssssss', $mail_template_name,$mail_template_subject, $mail_template_content,$cust_timage,$mail_content_type,$attachment,$mail_template_id);
	}
	else{
		$stmt = $conn->prepare("INSERT INTO tb_core_mailcamp_template_list(mail_template_id, mail_template_name, mail_template_subject, mail_template_content, cust_timage, mail_content_type, attachment, date) VALUES(?,?,?,?,?,?,?,?)");
		$stmt->bind_param('ssssssss', $mail_template_id,$mail_template_name,$mail_template_subject,$mail_template_content,$cust_timage,$mail_content_type,$attachment,$GLOBALS['entry_time']);
	}
	
	if ($stmt->execute() === TRUE){
		//Rename from .tmp to .att
		$mail_attachment = json_decode($attachment, true);
		foreach ($mail_attachment as $file_id => $file_name) {
			$file_name = "uploads/attachments/".$mail_template_id."_".$file_id;
			if(file_exists($file_name.'.tmp'))	//else => previously uploaded (update action of mail template)
		    	rename($file_name.'.tmp',$file_name.'.att');
		}
		deleteAttachment($conn,$mail_attachment);
		die('success'); 
	}
	else 
		die("failed"); 
}

function checkMailTemplateIdExist($conn,$mail_template_id){
	$stmt = $conn->prepare("SELECT COUNT(*) FROM tb_core_mailcamp_template_list where mail_template_id = ?");
	$stmt->bind_param("s", $mail_template_id);
	$stmt->execute();
	$row = $stmt->get_result()->fetch_row();
	if($row[0] > 0)
		return true;
	else
		return false;
}

function getMailTemplateList($conn){
	$result = mysqli_query($conn, "SELECT mail_template_id, mail_template_name, LEFT(mail_template_subject , 50) mail_template_subject, LEFT(mail_template_content , 50) mail_template_content,attachment,date FROM tb_core_mailcamp_template_list");
	header('Content-Type: application/json');
	if(mysqli_num_rows($result) > 0)		
		echo json_encode(mysqli_fetch_all($result, MYSQLI_ASSOC));
	else
		echo '{}';	
}

function getMailTemplateFromTemplateId($conn,$mail_template_id){
	$stmt = $conn->prepare("SELECT * FROM tb_core_mailcamp_template_list where mail_template_id = ?");
	$stmt->bind_param("s", $mail_template_id);
	$stmt->execute();
	$result = $stmt->get_result();
	header('Content-Type: application/json');
	if($result->num_rows != 0){
		$row = $result->fetch_assoc() ;
		echo json_encode($row) ;
	}			
	$stmt->close();
}

function deleteMailTemplateFromTemplateId($conn,$mail_template_id){	
	$stmt = $conn->prepare("DELETE FROM tb_core_mailcamp_template_list WHERE mail_template_id = ?");
	$stmt->bind_param("s", $mail_template_id);
	$stmt->execute();
	if($stmt->affected_rows != 0)
		echo "deleted";
	else
		echo "error";
	$stmt->close();
	removeTrackerImage($mail_template_id,true);
}

function makeCopyMailTemplate($conn){
	$old_mail_template_id = $_POST['mail_template_id'];
	$new_mail_template_id = $_POST['new_mail_template_id'];
	$new_mail_template_name = $_POST['new_mail_template_name'];

	$stmt = $conn->prepare("INSERT INTO tb_core_mailcamp_template_list (mail_template_id,mail_template_name,mail_template_subject,mail_template_content,attachment,date) SELECT ?, ?, mail_template_subject,mail_template_content,attachment,? FROM tb_core_mailcamp_template_list WHERE mail_template_id=?");
	$stmt->bind_param("ssss", $new_mail_template_id, $new_mail_template_name, $GLOBALS['entry_time'], $old_mail_template_id);
	
	if ($stmt->execute() === TRUE)
			die('success'); 
		else 
			die("failed"); 
	$stmt->close();
}

function uploadTrackerImage($conn){
	$file_extn =  pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);	
	$target_file = 'uploads/timages/img_'.$_POST['mail_template_id'].'.tmp';

    $check = getimagesize($_FILES["file"]["tmp_name"]);
    if($check !== false) {
        if (move_uploaded_file($_FILES["file"]["tmp_name"], $target_file)) {
	        echo "success";
	    } else{
	    	header("HTTP/1.0 400 Bad Request");
	    	echo "Upload error";
	    }
    } else{
	    	header("HTTP/1.0 400 Bad Request");
	    	echo "Invalid file";
	    }	
}

function removeTrackerImage($mail_template_id,$quite){	
	if (!empty(glob("uploads/timages/img_".$_POST['mail_template_id']."*")))
		array_map('unlink', array_filter((array) glob("uploads/timages/img_".$_POST['mail_template_id']."*")));
	if(!$quite)
		die('success');
}

function uploadAttachment($conn){
	$file_name = time();
	$target_file = 'uploads/attachments/'.$_POST['mail_template_id'].'_'.$file_name.'.tmp';

	header('Content-Type: application/json');
	if (move_uploaded_file($_FILES["file"]["tmp_name"], $target_file)) {
        echo '{"resp":"success","file_id":"'.$file_name.'"}';
    } else
    	echo '{"resp":"error"}';
}
function deleteAttachment($conn,$mail_attachment=''){
	$mail_template_id = $_POST['mail_template_id'];
	if(checkMailTemplateIdExist($conn,$mail_template_id)){	
		if(isset($_POST['file_name']))	//removes .att files during update of mail template
			unlink('uploads/attachments/'.$mail_template_id.'_'.$_POST['file_name'].'.tmp');
		else{	//removes .tmp file on x button click (for saved mail template)
			$attr_files = array_map('basename', glob('uploads/attachments/'.$mail_template_id.'_*.att'));
			$att_file_arr = [];

			foreach($mail_attachment as $file_id => $file_name)	//Creates file name array
		   		array_push($att_file_arr,$mail_template_id.'_'.$file_id.'.att');
			
			foreach ($attr_files as $file)
				if (!in_array($file, $att_file_arr)) {
			    	unlink('uploads/attachments/'.$file);
				}
		}		
	}
	else{	//removes .tmp file on x button click (for new mail template)
		$target_file = 'uploads/attachments/'.$mail_template_id.'_'.$_POST['file_name'].'.tmp';
		if(unlink($target_file))
	    	die("success");
	    else
	    	die("failed");
	}
}
//---------------------------------------Sender List Section --------------------------------
function saveSenderList($conn){
	$sender_list_id = $_POST['sender_list_id'];
	if($sender_list_id == '')
		$sender_list_id = null;
	$sender_list_mail_sender_name = $_POST['sender_list_mail_sender_name'];
	$sender_list_mail_sender_SMTP_server = base64_decode($_POST['sender_list_mail_sender_SMTP_server']);
	$sender_list_mail_sender_from = base64_decode($_POST['sender_list_mail_sender_from']);
	$sender_list_mail_sender_acc_username = base64_decode($_POST['sender_list_mail_sender_acc_username']);
	$sender_list_mail_sender_acc_pwd = base64_decode($_POST['sender_list_mail_sender_acc_pwd']);
	$mail_sender_mailbox = base64_decode($_POST['mail_sender_mailbox']);
	$sender_list_cust_headers = base64_decode($_POST['sender_list_cust_headers']);

	if(checkSenderListIdExist($conn,$sender_list_id)){
		if($sender_list_mail_sender_acc_pwd != ''){	//new sender acc pwd
			$stmt = $conn->prepare("UPDATE tb_core_mailcamp_sender_list SET sender_name=?, sender_SMTP_server=?, sender_from=?, sender_acc_username=?, sender_acc_pwd=?, sender_mailbox=?, cust_headers=? WHERE sender_list_id=?");
			$stmt->bind_param('ssssssss', $sender_list_mail_sender_name,$sender_list_mail_sender_SMTP_server,$sender_list_mail_sender_from,$sender_list_mail_sender_acc_username,$sender_list_mail_sender_acc_pwd,$mail_sender_mailbox,$sender_list_cust_headers,$sender_list_id);
		}
		else{	//sender acc pwd has no change
			$stmt = $conn->prepare("UPDATE tb_core_mailcamp_sender_list SET sender_name=?, sender_SMTP_server=?, sender_from=?, sender_acc_username=?, sender_mailbox=?, cust_headers=? WHERE sender_list_id=?");
			$stmt->bind_param('sssssss', $sender_list_mail_sender_name,$sender_list_mail_sender_SMTP_server,$sender_list_mail_sender_from,$sender_list_mail_sender_acc_username,$mail_sender_mailbox,$sender_list_cust_headers,$sender_list_id);
		}
	}
	else{
		$stmt = $conn->prepare("INSERT INTO tb_core_mailcamp_sender_list(sender_list_id,sender_name,sender_SMTP_server,sender_from,sender_acc_username,sender_acc_pwd,cust_headers,date) VALUES(?,?,?,?,?,?,?,?)");
		$stmt->bind_param('ssssssss', $sender_list_id,$sender_list_mail_sender_name,$sender_list_mail_sender_SMTP_server,$sender_list_mail_sender_from,$sender_list_mail_sender_acc_username,$sender_list_mail_sender_acc_pwd,$sender_list_cust_headers,$GLOBALS['entry_time']);
	}
	
	if ($stmt->execute() === TRUE)
		die('success'); 
	else 
		die("failed"); 
}

function checkSenderListIdExist($conn,$sender_list_id){
	$stmt = $conn->prepare("SELECT COUNT(*) FROM tb_core_mailcamp_sender_list where sender_list_id = ?");
	$stmt->bind_param("s", $sender_list_id);
	$stmt->execute();
	$row = $stmt->get_result()->fetch_row();
	if($row[0] > 0)
		return true;
	else
		return false;
}

function getSenderList($conn){
	$result = mysqli_query($conn, "SELECT sender_list_id,sender_name,sender_SMTP_server,sender_from,sender_acc_username,sender_mailbox,cust_headers,date FROM tb_core_mailcamp_sender_list");
	if(mysqli_num_rows($result) > 0){
		header('Content-Type: application/json');
		echo json_encode(mysqli_fetch_all($result, MYSQLI_ASSOC));
	}
}

function getSenderFromSenderListId($conn,$sender_list_id){
	$stmt = $conn->prepare("SELECT sender_name,sender_SMTP_server,sender_from,sender_acc_username,sender_mailbox,cust_headers FROM tb_core_mailcamp_sender_list where sender_list_id = ?");
	$stmt->bind_param("s", $sender_list_id);
	$stmt->execute();
	$result = $stmt->get_result();
	if($result->num_rows > 0){
		$row = $result->fetch_assoc() ;
		header('Content-Type: application/json');
		echo json_encode($row) ;
	}			
	$stmt->close();
}

function deleteMailSenderListFromSenderId($conn,$sender_list_id){	
	$stmt = $conn->prepare("DELETE FROM tb_core_mailcamp_sender_list WHERE sender_list_id = ?");
	$stmt->bind_param("s", $sender_list_id);
	$stmt->execute();
	if($stmt->affected_rows != 0)
		echo "deleted";
	else
		echo "error";
	$stmt->close();
}

function makeCopyMailSenderList($conn){
	$old_sender_list_id = $_POST['sender_list_id'];
	$new_sender_list_id = $_POST['new_sender_list_id'];
	$new_sender_list_name = $_POST['new_sender_list_name'];

	$stmt = $conn->prepare("INSERT INTO tb_core_mailcamp_sender_list (sender_list_id,sender_name,sender_SMTP_server,sender_from,sender_acc_username,sender_acc_pwd,sender_mailbox,cust_headers,date) SELECT ?, ?, sender_SMTP_server,sender_from,sender_acc_username,sender_acc_pwd,sender_mailbox,cust_headers,? FROM tb_core_mailcamp_sender_list WHERE sender_list_id=?");
	$stmt->bind_param("ssss", $new_sender_list_id, $new_sender_list_name, $GLOBALS['entry_time'], $old_sender_list_id);
	
	if ($stmt->execute() === TRUE)
			die('success'); 
		else 
			die("failed"); 
	$stmt->close();
}
?>
