<?php 
ini_set('max_execution_time', 0);
require_once(dirname(__FILE__,2) . '/config/db.php');
require_once(dirname(__FILE__,2) . '/manager/common_functions.php');
require_once(dirname(__FILE__,2) . '/libs/symfony/autoload.php');
require_once(dirname(__FILE__,2) . '/libs/qr_barcode/qrcode.php');
require_once(dirname(__FILE__,2) . '/libs/qr_barcode/barcode.php');
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Crypto\SMimeSigner;
use Symfony\Component\Mime\Crypto\SMimeEncrypter;
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
	$stmt = $conn->prepare("SELECT sender_name,sender_SMTP_server,sender_from,sender_acc_username,sender_acc_pwd,cust_headers,dsn_type FROM tb_core_mailcamp_sender_list WHERE sender_list_id = ?");
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

function generateRID(&$conn, &$campaign_id){ //this make 100% unique RID
	do{
		$RID = getRandomStr(10);

		$stmt = $conn->prepare("SELECT COUNT(*) FROM tb_data_mailcamp_live WHERE rid=? AND campaign_id=?");
		$stmt->bind_param("ss", $RID,$campaign_id);
		$stmt->execute();
		$row = $stmt->get_result()->fetch_row();
		if($row[0] == 0)
			break;
	}while(true);
	return $RID;
}

function InitMailCampaign($conn, $campaign_id){
	$keyword_vals = array();
	$i=0; // user counter

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
	$sender_SMTP_server = $MSENDER_DATA['sender_SMTP_server'];
	$sender_from_name = explode("<", $MSENDER_DATA['sender_from'])[0];
	$sender_from_mail = preg_match("/[\._a-zA-Z0-9-]+@[\._a-zA-Z0-9-]+/i", $MSENDER_DATA['sender_from'], $matches);
	$sender_from_mail = $matches[0];
	$sender_acc_username = $MSENDER_DATA['sender_acc_username'];
	$sender_acc_pwd = $MSENDER_DATA['sender_acc_pwd'];
	$cust_headers = $MSENDER_DATA['cust_headers'];
	$sender_dsn_type = $MSENDER_DATA['dsn_type'];

	$MCONFIG_DATA = getCONFIG($conn, $MC_mail_config_id);
	$config_mconfig_name = $MCONFIG_DATA['mconfig_name'];
	$config_recipient_type = $MCONFIG_DATA['mconfig_data']['recipient_type'];
	$config_signed_mail = $MCONFIG_DATA['mconfig_data']['signed_mail'];
	$config_encrypted_mail = $MCONFIG_DATA['mconfig_data']['encrypted_mail'];
	$config_antiflood_limit = $MCONFIG_DATA['mconfig_data']['antiflood']['limit'];
	$config_antiflood_pause = $MCONFIG_DATA['mconfig_data']['antiflood']['pause'];
	$config_msg_priority = $MCONFIG_DATA['mconfig_data']['msg_priority'];
	$config_peer_verification = $MCONFIG_DATA['mconfig_data']['peer_verification'];
	if($config_signed_mail){
		$config_mail_sign_cert_name = $MCONFIG_DATA['mconfig_data']['mail_sign']['cert']['name'];
		$config_mail_sign_cert_fb64 = $MCONFIG_DATA['mconfig_data']['mail_sign']['cert']['fb64'];
		$config_mail_sign_pvk_name = $MCONFIG_DATA['mconfig_data']['mail_sign']['pvk']['name'];
		$config_mail_sign_pvk_fb64 = $MCONFIG_DATA['mconfig_data']['mail_sign']['pvk']['fb64'];
		$config_mail_sign_pvk_passphrase = $MCONFIG_DATA['mconfig_data']['mail_sign']['pvk']['pvk_passphrase'];
	}
	if($config_encrypted_mail){
		$config_mail_enc_cert_name = $MCONFIG_DATA['mconfig_data']['mail_enc']['cert']['name'];
		$config_mail_enc_cert_fb64 = $MCONFIG_DATA['mconfig_data']['mail_enc']['cert']['fb64'];
	}

	$serv_variables = getServerVariable($conn);
	//----------------------------------------------------------------------------------------
	
	$transport = Transport::fromDsn(getMailerDSN($sender_dsn_type, urlencode($sender_acc_username), urlencode($sender_acc_pwd), $sender_SMTP_server, $config_peer_verification));
	$mailer = new Mailer($transport); 
	$message = (new Email());

	$message->priority($config_msg_priority); //Msg priority

	//Adding headers
  	foreach ($cust_headers as $header_name => $header_val) {
  		if(strcasecmp($header_name, 'return-path') == 0)
            $message->returnPath($header_val);
        elseif(strcasecmp($header_name, 'reply-to') == 0)
            $message->replyTo($header_val);
        else
            $message->getHeaders()->addTextHeader($header_name, $header_val);
	}

	foreach ($arr_user_data as $arr_user) {
		$send_time = round(microtime(true) * 1000); //milli-seconds
    	$msg_fail_retry_counter = 0;
	    $RID = generateRID($conn, $campaign_id); 
	    $i++;

	    $keyword_vals['{{RID}}'] = $RID;
	    $keyword_vals['{{MID}}'] = $campaign_id;
	    $keyword_vals['{{NAME}}'] = $arr_user['fname'].' '.$arr_user['lname'];
	    $keyword_vals['{{FNAME}}'] = $arr_user['fname'];
	    $keyword_vals['{{LNAME}}'] = $arr_user['lname'];
	    $keyword_vals['{{NOTES}}'] = $arr_user['notes'];
	    $keyword_vals['{{EMAIL}}'] = $arr_user['email'];
	    $keyword_vals['{{FROM}}'] = $sender_from_mail;
	    $keyword_vals['{{TRACKINGURL}}'] = $serv_variables['baseurl'].'/tmail?mid='.$campaign_id.'&rid='.$RID;
	    $keyword_vals['{{TRACKER}}'] = '<img src="'.$keyword_vals['{{TRACKINGURL}}'].'"/>';
	    $keyword_vals['{{BASEURL}}'] = $serv_variables['baseurl'];
	    $keyword_vals['{{MUSERNAME}}'] = explode('@', $arr_user['email'])[0];
	    $keyword_vals['{{MDOMAIN}}'] = explode('@', $arr_user['email'])[1];

	    //Add create trackable Msg id
	    if($message->getHeaders()->has('Message-ID'))
	    	$message->getHeaders()->remove('Message-ID');
	    $message->getHeaders()->addIdHeader('Message-ID', $RID.'@spmailer.generated');
	
		// Create a message
		$message->from(new Address($sender_from_mail, $sender_from_name))->subject((filterKeywords($mail_template_subject,$keyword_vals)));
		$msg_body = filterKeywords($mail_template_content,$keyword_vals);  	
		$msg_body = filterQRBarCode($msg_body,$keyword_vals,$message);
		if($mail_content_type == 'text/html')
            $message->html($msg_body);
        else
            $message->text($msg_body);

	  	statusEntryCreate($conn,$RID,$campaign_id,$MC_name,$send_time,$keyword_vals['{{NAME}}'],$keyword_vals['{{EMAIL}}']); 
	  	try{
		  	if($config_recipient_type == "to")
		  		$message->to($arr_user['email']);
		  	if($config_recipient_type == "cc")
		  		$message->cc($arr_user['email']);
		  	if($config_recipient_type == "bcc")
		  		$message->bcc($arr_user['email']);
		  	}catch(Exception $e) {
		  		statusEntryUpdate($conn, $RID, 3, json_encode([$e->getMessage()]));	//3=Error in sending due to address format
				continue;
		}

		//Add attachments
		foreach ($mail_attachment as $attachment) {
			$file_path = '../uploads/attachments/'.$attachment['file_id'].'.att';
			$file_disp_name = $attachment['file_disp_name'];

			if(preg_match('/{{[0-9]*[a-zA-Z]+[a-zA-Z0-9]*}}/i', $file_disp_name) == true)//if name has keywords
				$file_disp_name = filterKeywords($file_disp_name,$keyword_vals);
			
		    if($attachment['inline'])
		    	$message->embedFromPath($file_path, $file_disp_name);
		    else
		    	$message->attachFromPath($file_path, $file_disp_name);			
		}
	  	
		//-------------Start Signing & Encryption-------------
      	if($config_signed_mail){
    	  	$temp_mail_sign_cert = tmpfile();
    	  	fwrite($temp_mail_sign_cert, base64_decode($config_mail_sign_cert_fb64));
    	  	$temp_mail_sign_cert_path = stream_get_meta_data($temp_mail_sign_cert)['uri'];
    
    	  	$temp_mail_sign_pvk = tmpfile();
    	  	fwrite($temp_mail_sign_pvk, base64_decode($config_mail_sign_pvk_fb64));
    	  	$temp_mail_sign_pvk_path = stream_get_meta_data($temp_mail_sign_pvk)['uri'];
    	  	
    	  	$signer = new SMimeSigner($temp_mail_sign_cert_path, $temp_mail_sign_pvk_path, $config_mail_sign_pvk_passphrase);
    		$message = $signer->sign($message);
    	}

		//Encrypted mail
		if($config_encrypted_mail){
			$temp_mail_enc_cert = tmpfile();
		  	fwrite($temp_mail_enc_cert, base64_decode($config_mail_enc_cert_fb64));
		  	$temp_mail_enc_cert_path = stream_get_meta_data($temp_mail_enc_cert)['uri'];

			$encrypter = new SMimeEncrypter($temp_mail_enc_cert_path);
			$message = $encrypter->encrypt($message);	
		}    	    
    	//-------------End Signing & Encryption-------------
		
	  	while($msg_fail_retry_counter <= $MC_msg_fail_retry){
			// Send the message
			try{
				$result = $mailer->send($message);	//$failures will store the rejected addresses
				statusEntryUpdate($conn, $RID, 2);	//2= mail sent successfully
				break;
			}catch(Exception $e) {
				statusEntryUpdate($conn, $RID, 3, json_encode([$e->getMessage()]));	//3=Error in sending due to transport exception
				$msg_fail_retry_counter++;
				sleep(1); // give 1 sec delay before next attempt
			}
		}

		//sleep for next email
		$delay_val = rand(explode("-",$MC_msg_interval)[0]*1000,explode("-",$MC_msg_interval)[1]*1000); //milli-seconds
		usleep($delay_val*1000); //usleep is in microseconds

		//Anti-flood control
		if($i%$config_antiflood_limit == 0){
			$transport->stop();
			sleep($config_antiflood_pause);
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

function statusEntryCreate(&$conn,$rid,$campaign_id,$MC_name,$send_time,$user_name,$user_email){
	$stmt = $conn->prepare("INSERT INTO tb_data_mailcamp_live(rid,campaign_id,campaign_name,sending_status,send_time,user_name,user_email) VALUES(?,?,?,1,?,?,?)"); //1= in progress
	$stmt->bind_param('ssssss', $rid,$campaign_id,$MC_name,$send_time,$user_name,$user_email);
	$stmt->execute();
}

function statusEntryUpdate(&$conn,$rid,$sending_status,$send_error=null){
	if($send_error == null){
		$stmt = $conn->prepare("UPDATE tb_data_mailcamp_live SET sending_status=? WHERE rid=?");
		$stmt->bind_param('ss', $sending_status, $rid);
	}
	else{
		$stmt = $conn->prepare("UPDATE tb_data_mailcamp_live SET sending_status=?, send_error=? WHERE rid=?");
		$stmt->bind_param('sss', $sending_status, $send_error, $rid);
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
//InitMailCampaign($conn, $argv[1]);	//For console testing
?>
