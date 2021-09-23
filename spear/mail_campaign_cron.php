<?php 
ini_set('max_execution_time', 0);
require_once(dirname(__FILE__) . '/db.php');
require_once(dirname(__FILE__) . '/common_functions.php');
require_once(dirname(__FILE__) . '/libs/swiftmailer/autoload.php');
require_once(dirname(__FILE__) . '/libs/qr_barcode/qrcode.php');
require_once(dirname(__FILE__) . '/libs/qr_barcode/barcode.php');
date_default_timezone_set('UTC');
$entry_time = (new DateTime())->format('d-m-Y h:i A');

function getMC($conn, $campaign_id){
	$stmt = $conn->prepare("SELECT campaign_name,campaign_data,date,scheduled_time,camp_status FROM tb_core_mailcamp_list WHERE campaign_id = ?");
	$stmt->bind_param("s", $campaign_id);
	$stmt->execute();
	$result = $stmt->get_result();
	if($row = $result->fetch_assoc()){
		$row["campaign_data"] = json_decode($row["campaign_data"],true);	//avoid double json encoding
		return ($row) ;
	}
	else
		die(json_encode(['result' => 'Invalid campaign id '.$campaign_id]));	
}

function getUSERGROUP($conn,$user_group_id){
	$stmt = $conn->prepare("SELECT user_group_name,user_data FROM tb_core_mailcamp_user_group WHERE user_group_id = ?");
	$stmt->bind_param("s", $user_group_id);
	$stmt->execute();
	$result = $stmt->get_result();
	if($row = $result->fetch_assoc()){
		$row['user_data'] = json_decode($row["user_data"],true);	//avoid double json encoding
		return ($row) ;
	}
	else
		die(json_encode(['result' => 'Invalid user group id '.$user_group_id]));	
}

function getMTEMPLATE($conn, $mail_template_id){
	$stmt = $conn->prepare("SELECT mail_template_name,mail_template_subject,mail_template_content,timage_type,mail_content_type,attachment FROM tb_core_mailcamp_template_list WHERE mail_template_id = ?");
	$stmt->bind_param("s", $mail_template_id);
	$stmt->execute();
	$result = $stmt->get_result();
	if($result->num_rows != 0){
		$row = $result->fetch_assoc() ;
		$row['attachment'] = json_decode($row['attachment'],true);
		return $row;
	}
	else
		die(json_encode(['result' => 'Invalid mailtemplate id '.$mail_template_id]));	
}

function getMSENDER($conn, $mail_sender_id){
	$stmt = $conn->prepare("SELECT sender_name,sender_SMTP_server,sender_from,sender_acc_username,sender_acc_pwd,smtp_enc_level,cust_headers FROM tb_core_mailcamp_sender_list WHERE sender_list_id = ?");
	$stmt->bind_param("s", $mail_sender_id);
	$stmt->execute();
	$result = $stmt->get_result();
	if($result->num_rows != 0){
		$row = $result->fetch_assoc() ;
		$row["cust_headers"] = json_decode($row["cust_headers"],true);	//avoid double json encoding
		return $row;
	}
	else
		die(json_encode(['result' => 'Invalid mailtemplate id '.$mail_sender_id]));	
}

function getCONFIG($conn, $mconfig_id){
	$stmt = $conn->prepare("SELECT mconfig_name,mconfig_data FROM tb_core_mailcamp_config WHERE mconfig_id = ?");
	$stmt->bind_param("s", $mconfig_id);
	$stmt->execute();
	$result = $stmt->get_result();
	if($result->num_rows != 0){
		$row = $result->fetch_assoc();
		$row["mconfig_data"] = json_decode($row["mconfig_data"],true);	//avoid double json encoding
		return $row;
	}
	else
		die(json_encode(['result' => 'Invalid mail campaign configuration id '.$mconfig_id]));	
}

function generateCID(&$conn, &$campaign_id){ //this make 100% unique CID
	do{
		$CID = getRandomStr(10);

		$stmt = $conn->prepare("SELECT COUNT(*) FROM tb_data_mailcamp_live WHERE id=? AND campaign_id=?");
		$stmt->bind_param("ss", $CID,$campaign_id);
		$stmt->execute();
		$row = $stmt->get_result()->fetch_row();
		if($row[0] == 0)
			break;
	}while(true);
	return $CID;
}

function InitMailCampaign($conn, $campaign_id){
	$keyword_vals = array();

	$MC_DATA = getMC($conn, $campaign_id);
	$MC_name = $MC_DATA['campaign_name'];
	$MC_user_group_id = $MC_DATA['campaign_data']['user_group']['id'];
	$MC_user_group_name = $MC_DATA['campaign_data']['user_group']['name'];
	$MC_mail_template_id = $MC_DATA['campaign_data']['mail_template']['id'];
	$MC_mail_template_name = $MC_DATA['campaign_data']['mail_template']['name'];
	$MC_mail_sender_id = $MC_DATA['campaign_data']['mail_sender']['id'];
	$MC_mail_sender_name = $MC_DATA['campaign_data']['mail_sender']['name'];
	$MC_mail_config_id = $MC_DATA['campaign_data']['mail_config']['id'];
	$MC_mail_config_name = $MC_DATA['campaign_data']['mail_config']['name'];
	$MC_scheduled_time = $MC_DATA['scheduled_time'];
	$MC_msg_interval = $MC_DATA['campaign_data']['msg_interval'];
	$MC_msg_fail_retry = $MC_DATA['campaign_data']['msg_fail_retry'];
	$MC_status = $MC_DATA['camp_status'];

	$MUSERGROUP_DATA =  getUSERGROUP($conn,$MC_user_group_id);
	$arr_user_data =  $MUSERGROUP_DATA['user_data'];

	$MTEMPLATE_DATA = getMTEMPLATE($conn, $MC_mail_template_id);
	$mail_template_name = $MTEMPLATE_DATA['mail_template_name'];
	$mail_template_subject = $MTEMPLATE_DATA['mail_template_subject'];
	$mail_timage_type = $MTEMPLATE_DATA['timage_type'];
	$mail_template_content = $MTEMPLATE_DATA['mail_template_content'];
	$mail_content_type = $MTEMPLATE_DATA['mail_content_type'];
	$mail_attachment = $MTEMPLATE_DATA['attachment'];

	$MSENDER_DATA = getMSENDER($conn, $MC_mail_sender_id);
	$sender_name = $MSENDER_DATA['sender_name'];
	$sender_SMTP_server_ip = explode(":",$MSENDER_DATA['sender_SMTP_server'])[0];
	$sender_SMTP_server_port = explode(":",$MSENDER_DATA['sender_SMTP_server'])[1];
	$sender_from_name = explode("<", $MSENDER_DATA['sender_from'])[0];
	$sender_from_mail = preg_match("/[\._a-zA-Z0-9-]+@[\._a-zA-Z0-9-]+/i", $MSENDER_DATA['sender_from'], $matches);
	$sender_from_mail = $matches[0];
	$sender_acc_username = $MSENDER_DATA['sender_acc_username'];
	$sender_acc_pwd = $MSENDER_DATA['sender_acc_pwd'];
	$sender_smtp_enc_level = $MSENDER_DATA['smtp_enc_level'];
	$cust_headers = $MSENDER_DATA['cust_headers'];

	$MCONFIG_DATA = getCONFIG($conn, $MC_mail_config_id);
	$config_mconfig_name = $MCONFIG_DATA['mconfig_name'];
	$config_batch_mail_limit = $MCONFIG_DATA['mconfig_data']['batch_mail_limit'];
	$config_recipient_type = $MCONFIG_DATA['mconfig_data']['recipient_type'];
	$config_read_receipt = $MCONFIG_DATA['mconfig_data']['read_receipt'];
	$config_non_ascii_support = $MCONFIG_DATA['mconfig_data']['non_ascii_support'];
	$config_signed_mail = $MCONFIG_DATA['mconfig_data']['signed_mail'];
	$config_encrypted_mail = $MCONFIG_DATA['mconfig_data']['encrypted_mail'];
	$config_antiflood_limit = $MCONFIG_DATA['mconfig_data']['antiflood']['limit'];
	$config_antiflood_pause = $MCONFIG_DATA['mconfig_data']['antiflood']['pause'];
	$config_msg_priority = $MCONFIG_DATA['mconfig_data']['msg_priority'];
	if($config_signed_mail){
		$config_mail_sign_cert_name = $MCONFIG_DATA['mconfig_data']['mail_sign']['cert']['name'];
		$config_mail_sign_cert_fb64 = $MCONFIG_DATA['mconfig_data']['mail_sign']['cert']['fb64'];
		$config_mail_sign_pvk_name = $MCONFIG_DATA['mconfig_data']['mail_sign']['pvk']['name'];
		$config_mail_sign_pvk_fb64 = $MCONFIG_DATA['mconfig_data']['mail_sign']['pvk']['fb64'];
	}
	if($config_encrypted_mail){
		$config_mail_enc_cert_name = $MCONFIG_DATA['mconfig_data']['mail_enc']['cert']['name'];
		$config_mail_enc_cert_fb64 = $MCONFIG_DATA['mconfig_data']['mail_enc']['cert']['fb64'];
	}

	$serv_variables = getServerVariable($conn);
	//----------------------------------------------------------------------------------------
		
	if($sender_smtp_enc_level == 0)
		$transport = (new Swift_SmtpTransport($sender_SMTP_server_ip, $sender_SMTP_server_port)) ->setUsername($sender_acc_username) ->setPassword($sender_acc_pwd);
	else
		$transport = (new Swift_SmtpTransport($sender_SMTP_server_ip, $sender_SMTP_server_port, $sender_smtp_enc_level==1?"ssl":"tls")) ->setUsername($sender_acc_username) ->setPassword($sender_acc_pwd);
	$mailer = new Swift_Mailer($transport);

	//Internationalized Email Addresses
	if($config_non_ascii_support){
		$smtpUtf8 = new Swift_Transport_Esmtp_SmtpUtf8Handler();
		$transport->setExtensionHandlers([$smtpUtf8]);
		$utf8Encoder = new Swift_AddressEncoder_Utf8AddressEncoder();
		$transport->setAddressEncoder($utf8Encoder);
	}
	
	// Antiflood plugin
	$mailer->registerPlugin(new Swift_Plugins_AntiFloodPlugin($config_antiflood_limit, $config_antiflood_pause));

	$message = new Swift_Message();
  	$headers = $message->getHeaders();
	$message->setFrom([$sender_from_mail => $sender_from_name]);

	//Msg priority
	$message->setPriority($config_msg_priority);

	//Requesting a Read Receipt
	if($config_read_receipt)
		$message->setReadReceiptTo($sender_from_mail);

	//Adding headers
  	foreach ($cust_headers as $header_name => $header_val) {
  		if(strcasecmp($header_name, "Return-Path") == 0)
			$message->setReturnPath($header_val);
		else
			$headers->addTextHeader($header_name, $header_val);
	}

	//Add attachments
	$var_attachments=[];
	foreach ($mail_attachment as $attachment) {
		if(preg_match('/{{[0-9]*[a-zA-Z]+[a-zA-Z0-9]*}}/i', $attachment['file_disp_name']) == true){//if name has keywords
			$attachment['att_ob'] = null;	//adding field for attachment object to use below
			array_push($var_attachments,$attachment);
		}
		else{	
			$file_path = 'uploads/attachments/'.$attachment['file_id'].'.att';
		    if($attachment['inline'])
		    	$message->attach(Swift_Attachment::fromPath($file_path,mime_content_type($file_path))->setFilename($attachment['file_disp_name'])->setDisposition('inline'));
		    else
		    	$message->attach(Swift_Attachment::fromPath($file_path,mime_content_type($file_path))->setFilename($attachment['file_disp_name']));
		}
	}

	//-------------Start Signing & Encryption-------------
	if($config_signed_mail || $config_encrypted_mail)
  		$smimeSigner = new Swift_Signers_SMimeSigner();
  	//Signed mail
  	if($config_signed_mail){
	  	$temp_mail_sign_cert = tmpfile();
	  	fwrite($temp_mail_sign_cert, base64_decode($config_mail_sign_cert_fb64));
	  	$temp_mail_sign_cert_path = stream_get_meta_data($temp_mail_sign_cert)['uri'];

	  	$temp_mail_sign_pvk = tmpfile();
	  	fwrite($temp_mail_sign_pvk, base64_decode($config_mail_sign_pvk_fb64));
	  	$temp_mail_sign_pvk_path = stream_get_meta_data($temp_mail_sign_pvk)['uri'];
	  	
		$smimeSigner->setSignCertificate($temp_mail_sign_cert_path, $temp_mail_sign_pvk_path);
	}
	//Encrypted mail
	if($config_encrypted_mail){
		$temp_mail_enc_cert = tmpfile();
	  	fwrite($temp_mail_enc_cert, base64_decode($config_mail_enc_cert_fb64));
	  	$temp_mail_enc_cert_path = stream_get_meta_data($temp_mail_enc_cert)['uri'];

		$smimeSigner->setEncryptCertificate($temp_mail_enc_cert_path);		
	}

	if($config_signed_mail || $config_encrypted_mail)
		$message->attachSigner($smimeSigner);
	//-------------End Signing & Encryption-------------

	foreach ($arr_user_data as $i  => $arr_user) {
		$send_time = round(microtime(true) * 1000); //milli-seconds
    	$msg_fail_retry_counter = 0;
	    $CID = generateCID($conn, $campaign_id); 

	    $keyword_vals['{{CID}}'] = $CID;
	    $keyword_vals['{{MID}}'] = $campaign_id;
	    $keyword_vals['{{NAME}}'] = $arr_user['name'];
	    $keyword_vals['{{FNAME}}'] = explode(' ', $arr_user['name'])[0];
	    $keyword_vals['{{LNAME}}'] = count(explode(' ', $arr_user['name'],2)) == 2?explode(' ', $arr_user['name'],2)[1]:"";
	    $keyword_vals['{{NOTES}}'] = $arr_user['notes'];
	    $keyword_vals['{{EMAIL}}'] = $arr_user['email'];
	    $keyword_vals['{{FROM}}'] = $sender_from_mail;
	    $keyword_vals['{{TRACKINGURL}}'] = $serv_variables['baseurl'].'/tmail?mid='.$campaign_id.'&cid='.$CID;
	    $keyword_vals['{{TRACKER}}'] = '<img src="'.$keyword_vals['{{TRACKINGURL}}'].'"/>';
	    $keyword_vals['{{BASEURL}}'] = $serv_variables['baseurl'];
	    $keyword_vals['{{MUSERNAME}}'] = explode('@', $arr_user['email'])[0];
	    $keyword_vals['{{MDOMAIN}}'] = explode('@', $arr_user['email'])[1];
	
		// Create a message
		$message->setSubject((filterKeywords($mail_template_subject,$keyword_vals)));	  	
		$msg_body = filterKeywords($mail_template_content,$keyword_vals);  	
		$msg_body = filterQRBarCode($msg_body,$keyword_vals,$message);
	  	$message->setBody($msg_body,$mail_content_type);
	  	$message->setId($CID.'@sniperphish.generated');

	  	//add variable attachments
	  	foreach ($var_attachments as $key  => $attachment) {
			$file_path = 'uploads/attachments/'.$attachment['file_id'].'.att';
			$file_disp_name = filterKeywords($attachment['file_disp_name'],$keyword_vals);

		    if($attachment['inline'])
		    	$att = Swift_Attachment::fromPath($file_path,mime_content_type($file_path))->setFilename($file_disp_name)->setDisposition('inline');
		    else
		    	$att = Swift_Attachment::fromPath($file_path,mime_content_type($file_path))->setFilename($file_disp_name);


			if($attachment['att_ob'] != null)
				$message->detach($attachment['att_ob']);
		    $var_attachments[$key]['att_ob'] = $att;
		    $message->attach($att);			
		}

	  	statusEntryCreate($conn,$CID,$campaign_id,$MC_name,$send_time,$arr_user['name'],$arr_user['email']); 
	  	try{
		  	if($config_recipient_type == "to")
		  		$message->setTo([$arr_user['email']]);
		  	if($config_recipient_type == "cc")
		  		$message->setCc([$arr_user['email']]);
		  	if($config_recipient_type == "bcc")
		  		$message->setBcc([$arr_user['email']]);
		  	}catch(Exception $e) {
		  		statusEntryUpdate($conn, $CID, 3, json_encode([$e->getMessage()]));	//3=Error in sending due to address format
				continue;
		}
	  	
	  	while($msg_fail_retry_counter <= $MC_msg_fail_retry){
			// Send the message
			try{
				$result = $mailer->send($message);	//$failures will store the rejected addresses
				if($result){
					statusEntryUpdate($conn, $CID, 2);	//2= mail sent successfully
					break;
				}
				else{			
					statusEntryUpdate($conn, $CID, 3, 'Error');	//3=Error in sending
					$msg_fail_retry_counter++;
					sleep(1); // give 1 sec delay before next attempt
				}
			}catch(Exception $e) {
				statusEntryUpdate($conn, $CID, 3, json_encode([$e->getMessage()]));	//3=Error in sending due to transport exception
				$msg_fail_retry_counter++;
				$transport->stop();	//should stop if exception happen
				sleep(1); // give 1 sec delay before next attempt
			}
		}

		//sleep for next email
		$delay_val = rand(explode("-",$MC_msg_interval)[0]*1000,explode("-",$MC_msg_interval)[1]*1000); //milli-seconds
		if(($i+1)%$config_batch_mail_limit == 0){
			$delay_took = round(microtime(true) * 1000) - $send_time; //now (after send time)
			if($delay_took < $delay_val)
				usleep(($delay_val - $delay_took)*1000); //usleep is in microseconds
		}

		//Exit if campaign is stopped by user
		if(isCampaignStopped($conn, $campaign_id))
			break;
	}
	changeCampaignStatus($conn, $campaign_id, 4); //4=Mail sending completed (But campaign is in progress (2))
}

function isCampaignStopped($conn, $campaign_id){
	$stmt = $conn->prepare("SELECT camp_status FROM tb_core_mailcamp_list WHERE campaign_id=?");
	$stmt->bind_param('s', $campaign_id);
	$stmt->execute();
	$row = $stmt->get_result()->fetch_assoc();
	if ($row['camp_status'] != 2)	// 2 - In Progress (mail progress and tracking progress)
		return true;	//stop
	else
		return false;	//continue
}

function statusEntryCreate(&$conn,$cid,$campaign_id,$MC_name,$send_time,$user_name,$user_email){
	$stmt = $conn->prepare("INSERT INTO tb_data_mailcamp_live(id,campaign_id,campaign_name,sending_status,send_time,user_name,user_email) VALUES(?,?,?,1,?,?,?)"); //1= in progress
	$stmt->bind_param('ssssss', $cid,$campaign_id,$MC_name,$send_time,$user_name,$user_email);
	$stmt->execute();
}

function statusEntryUpdate(&$conn,$cid,$sending_status,$send_error=null){
	if($send_error == null){
		$stmt = $conn->prepare("UPDATE tb_data_mailcamp_live SET sending_status=? WHERE id=?");
		$stmt->bind_param('ss', $sending_status, $cid);
	}
	else{
		$stmt = $conn->prepare("UPDATE tb_data_mailcamp_live SET sending_status=?, send_error=? WHERE id=?");
		$stmt->bind_param('sss', $sending_status, $send_error, $cid);
	}
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
		$scheduled_time = strtotime($row['scheduled_time'])*1000;	//ms time
		$current_time = microtime(true)*1000;	//ms time

		while($scheduled_time>$current_time){
			//wait time scheduled time comes
			usleep(1000);	//0.001 seconds. (in ms)
			$current_time = microtime(true)*1000;	//ms time
		}
		$stmt->close();

		//Run campaign
		changeCampaignStatus($conn, $campaign_id, 2);//Set status in-progress
		InitMailCampaign($conn, $campaign_id);
	}
}

function changeCampaignStatus($conn, $campaign_id, $status){
	$stmt = $conn->prepare("UPDATE tb_core_mailcamp_list SET camp_status = ? WHERE campaign_id=?");
	$stmt->bind_param('ss', $status, $campaign_id);
	if ($stmt->execute() === TRUE)
		return true; 
	else 
		return false;
}

lockAndWaitProcess($conn, $argv[1]);
//InitMailCampaign($conn, $argv[1]);
?>