<?php 
ini_set('max_execution_time', 18000);
require_once(dirname(__FILE__) . '/db.php');
require_once(dirname(__FILE__,2) . '/vendor/autoload.php');
date_default_timezone_set('UTC');
$entry_time = (new DateTime())->format('d-m-Y h:i A');

function changeCampaignStatus($conn, $campaign_id, $status){
	$stmt = $conn->prepare("UPDATE tb_core_mailcamp_list SET camp_status = ? where campaign_id=?");
	$stmt->bind_param('ss', $status, $campaign_id);
	if ($stmt->execute() === TRUE)
		return true; 
	else 
		return false;
}

function InitMailCampaign($conn, $campaign_id){
	$keyword_vals = array();
	$stmt = $conn->prepare("SELECT campaign_name,user_group,mail_template,mail_sender,date,scheduled_time,msg_interval,msg_fail_retry,camp_status FROM tb_core_mailcamp_list where campaign_id = ?");
	$stmt->bind_param("s", $campaign_id);
	$stmt->execute();
	$result = $stmt->get_result();
	if($rowMC = $result->fetch_assoc()){
		$MC_name = $rowMC['campaign_name'];
		$MC_user_group_id = explode(',',$rowMC['user_group'])[0];
		$MC_user_group_name = explode(',',$rowMC['user_group'])[1];
		$MC_mail_template_id = explode(',',$rowMC['mail_template'])[0];
		$MC_mail_template_name = explode(',',$rowMC['mail_template'])[1];
		$MC_mail_sender_id = explode(',',$rowMC['mail_sender'])[0];
		$MC_mail_sender_name = explode(',',$rowMC['mail_sender'])[1];
		$MC_scheduled_time = $rowMC['scheduled_time'];
		$MC_msg_interval = $rowMC['msg_interval'];
		$MC_msg_fail_retry = $rowMC['msg_fail_retry'];
		$MC_status = $rowMC['camp_status'];

		//------------------
		$stmt = $conn->prepare("SELECT mail_template_name,mail_template_subject,mail_template_content,mail_content_type,attachment FROM tb_core_mailcamp_template_list where mail_template_id = ?");
		$stmt->bind_param("s", $MC_mail_template_id);
		$stmt->execute();
		$result = $stmt->get_result();
		if($row = $result->fetch_assoc()){
			$mail_template_name = $row['mail_template_name'];
			$mail_template_subject = $row['mail_template_subject'];
			$mail_template_content = $row['mail_template_content'];
			$mail_content_type = $row['mail_content_type'];
			$mail_attachment = json_decode($row['attachment'], true);
		}
		else
			die("Unable to find email template ".$MC_mail_template_name);	
		//------------------
		$stmt = $conn->prepare("SELECT sender_name,sender_SMTP_server,sender_from,sender_acc_username,sender_acc_pwd,cust_headers FROM tb_core_mailcamp_sender_list where sender_list_id = ?");
		$stmt->bind_param("s", $MC_mail_sender_id);
		$stmt->execute();
		$result = $stmt->get_result();
		if($row = $result->fetch_assoc()){
			$sender_name = $row['sender_name'];
			$sender_SMTP_server_ip = explode(":",$row['sender_SMTP_server'])[0];
			$sender_SMTP_server_port = explode(":",$row['sender_SMTP_server'])[1];
			$sender_from_name = explode("<",$row['sender_from'])[0];
			$sender_from_mail = str_replace(">","",explode("<", $row['sender_from'])[1]);
			$sender_acc_username = $row['sender_acc_username'];
			$sender_acc_pwd = $row['sender_acc_pwd'];
			$cust_headers = array_filter(explode("$#$",$row['cust_headers']));
		}	
		else
			die("Unable to find email sender ".$MC_mail_sender_name);	
		//--------------------------------------
		$stmt = $conn->prepare("SELECT user_name,user_email,user_notes FROM tb_core_mailcamp_user_group where user_group_id = ?");
		$stmt->bind_param("s", $MC_user_group_id);
		$stmt->execute();
		$result = $stmt->get_result();
		$row = $result->fetch_assoc() ;
		if(!$row)
			die("Unable to find email user email accounts in user group ". $MC_user_group_name);

		$arr_names =  explode(",",$row['user_name']); // no filter since likely contains empty value
		$arr_emails =  array_filter(explode(",",$row['user_email']));
		$arr_notes =  explode(",",$row['user_notes']);// no filter since likely contains empty value
		
		foreach ($arr_emails as $index  => $mailto_user_email) {

			$delay_val = rand(explode("-",$MC_msg_interval)[0]*1000,explode("-",$MC_msg_interval)[1]*1000); //milli-seconds
		    $id = substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyz', ceil(10/strlen($x)) )),1,10);
		    $send_time = round(microtime(true) * 1000); //milli-seconds
		    $send_err = '';
		    $msg_fail_retry_tmp = 0;

		    $serv_variables = getServerVariable($conn);
		    $keyword_vals['{{cid}}'] = $id;
		    $keyword_vals['{{mid}}'] = $campaign_id;
		    $keyword_vals['{{name}}'] = $arr_names[$index];
		    $keyword_vals['{{notes}}'] = $arr_notes[$index];
		    $keyword_vals['{{email}}'] = $mailto_user_email;
		    $keyword_vals['{{from}}'] = $sender_from_mail;
		    $keyword_vals['{{trackingurl}}'] = $serv_variables['baseurl'].'/trackmail?mid='.$campaign_id.'&cid='.$id;
		    $keyword_vals['{{tracker}}'] = '<img src="'.$keyword_vals['{{trackingurl}}'].'"/>';
		    $keyword_vals['{{baseurl}}'] = $serv_variables['baseurl'];
		    ////////////////Updating start status///////////////
		    $stmt = $conn->prepare("INSERT INTO tb_data_mailcamp_live(id,campaign_id,campaign_name,sending_status,send_time,mailto_user_name,mailto_user_email) VALUES(?,?,?,1,?,?,?)"); //1= in progress
			$stmt->bind_param('ssssss', $id,$campaign_id,$MC_name,$send_time,$arr_names[$index],$mailto_user_email);
			$stmt->execute();
		
			////////////////Sending email////////////////
			$transport = (new Swift_SmtpTransport($sender_SMTP_server_ip, $sender_SMTP_server_port, 'ssl')) ->setUsername($sender_acc_username) ->setPassword($sender_acc_pwd);

			// Create the Mailer using your created Transport
			$mailer = new Swift_Mailer($transport);
			// Create a message
			$message = (new Swift_Message(filterKeywords($mail_template_subject,$keyword_vals)))
		  		->setFrom([$sender_from_mail => $sender_from_name])
		  		->setTo([$mailto_user_email])
		  		->setBody(filterKeywords($mail_template_content,$keyword_vals),$mail_content_type);

		  	//Set headers
			$headers = $message->getHeaders();	
			foreach ($cust_headers as $header) {
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

			//Add attachments
			foreach ($mail_attachment as $file_id => $file_name) {
				$file_path = 'uploads/attachments/'.$MC_mail_template_id.'_'.$file_id.'.att';
			    $message->attach(Swift_Attachment::fromPath($file_path,mime_content_type($file_path))->setFilename($file_name));
			}

			//echo $headers->toString();
			while($msg_fail_retry_tmp <= $MC_msg_fail_retry){
				try {
					// Send the message
					$result = $mailer->send($message);
					updateLiveStatus($conn,$id,2,$send_err);	//2= mail sent successfully
					break;					
				} catch (Exception $e) {
					$send_err .= "Attempt ".(++$msg_fail_retry_tmp).': '.$e->getMessage().'<br/><br/>';
					if($msg_fail_retry_tmp > $MC_msg_fail_retry){
			  			updateLiveStatus($conn,$id,3,$send_err);	//3=Error in sending
			  			break;
					}
					else
						slee(1); // give some delay nefore next attempt
				}
			}

			$delay_took = round(microtime(true) * 1000) - $send_time; //now-sent time
			if($delay_took < $delay_val)
				usleep(($delay_val - $delay_took)*1000); //usleep is in microseconds

		}
		$stmt = $conn->prepare("UPDATE tb_core_mailcamp_list SET camp_status=4 WHERE campaign_id=?"); //4=mail sending completed
		$stmt->bind_param('s', $campaign_id);
		$stmt->execute();

		//---------------------------------------
		$stmt->close();
	}	
	else
		echo("Unable to find mail campaign ".$campaign_id);		
}
function filterKeywords($content,$keyword_vals){
	$keywords = array("{{cid}}", "{{mid}}", "{{name}}", "{{notes}}", "{{email}}", "{{from}}", "{{trackingurl}}", "{{tracker}}", "{{baseurl}}");

	foreach($keywords as $keword) {
		switch(strtolower($keword))
		{
			case "{{cid}}" : $r_val = $keyword_vals['{{cid}}']; break;
			case "{{mid}}" : $r_val = $keyword_vals['{{mid}}']; break;
			case "{{name}}" : $r_val = $keyword_vals['{{name}}']; break;
			case "{{notes}}" : $r_val = $keyword_vals['{{notes}}']; break;
			case "{{email}}" : $r_val = $keyword_vals['{{email}}']; break;
			case "{{from}}" : $r_val = $keyword_vals['{{from}}']; break;
			case "{{trackingurl}}" : $r_val = $keyword_vals['{{trackingurl}}']; break;
			case "{{tracker}}" : $r_val = $keyword_vals['{{tracker}}']; break;
			case "{{baseurl}}" : $r_val = $keyword_vals['{{baseurl}}']; break;
		}
	  	$content = str_ireplace($keword,$r_val,$content);
	}
	return $content;
}

function getServerVariable($conn){
	$result = mysqli_query($conn, "SELECT server_protocol,domain,baseurl FROM tb_main_variables");
		if(mysqli_num_rows($result) > 0){
		return mysqli_fetch_all($result, MYSQLI_ASSOC)[0];
	}
}

function updateLiveStatus($conn,$id,$sending_status,$send_error){
	$stmt = $conn->prepare("UPDATE tb_data_mailcamp_live SET sending_status=?, send_error=? WHERE id=?");
	$stmt->bind_param('sss', $sending_status,$send_error,$id);
	$stmt->execute();
}

function lockAndWaitProcess($conn, $campaign_id){
	$stmt = $conn->prepare("SELECT scheduled_time,camp_lock FROM tb_core_mailcamp_list WHERE campaign_id=?");
	$stmt->bind_param('s', $campaign_id);
	$stmt->execute();
	$row = $stmt->get_result()->fetch_assoc();
	if ($row['camp_lock'] == 0){	// campaign not locked
		$stmt = $conn->prepare("UPDATE tb_core_mailcamp_list SET camp_lock=1 WHERE campaign_id=?");
		$stmt->bind_param('s', $campaign_id);
		$stmt->execute();	//Lock campaign
		
		//Wait for camp exec time
		$scheduled_time = date('d-m-Y h:i:s:u A',strtotime($row['scheduled_time']));	//ms time
		$current_time = (new DateTime())->format('d-m-Y h:i:s:u A');

		while($scheduled_time>$current_time){
			//wait time scheduled time comes
			usleep(10000);	//0.001 seconds. (in ms)
			$current_time = (new DateTime())->format('d-m-Y h:i:s:u A');
		}

		//Run campaign
		changeCampaignStatus($conn, $campaign_id, 2);//Set status in-progress
		InitMailCampaign($conn, $campaign_id);
	}
}

//checkMailCampaign($conn, $argv[1]);

lockAndWaitProcess($conn, $argv[1]);

?>