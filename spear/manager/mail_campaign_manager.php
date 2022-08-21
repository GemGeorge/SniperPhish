<?php
require_once(dirname(__FILE__) . '/session_manager.php');
require_once(dirname(__FILE__,2) . '/libs/tcpdf_min/tcpdf.php');
//-------------------------------------------------------
date_default_timezone_set('UTC');
$entry_time = (new DateTime())->format('d-m-Y h:i A');
header('Content-Type: application/json');

if (isset($_POST)) {
	$POSTJ = json_decode(file_get_contents('php://input'),true);

	if(isset($POSTJ['action_type'])){

		if(isSessionValid() == false){
			$OPS = ['get_campaign_from_campaign_list_id','multi_get_mcampinfo_from_mcamp_list_id_get_live_mcamp_data','get_mail_replied','get_user_group_data'];	//public permitted requests
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
			multi_get_mcampinfo_from_mcamp_list_id_get_live_mcamp_data($conn,$POSTJ);
		if($POSTJ['action_type'] == "download_report")
			downloadReport($conn, $POSTJ['campaign_id'],$POSTJ['selected_col'],$POSTJ['dic_all_col'],$POSTJ['file_name'],$POSTJ['file_format'],$POSTJ['tb_data_single']);
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
		echo json_encode(['result' => 'success']);	
	}
	else 
		echo json_encode(['result' => 'failed', 'error' => $stmt->error]);	
}

function getCampaignList($conn){
	$resp = [];
	$DTime_info = getTimeInfo($conn);

	$result = mysqli_query($conn, "SELECT campaign_id,campaign_name,campaign_data,date,scheduled_time,stop_time,camp_status FROM tb_core_mailcamp_list");
	if(mysqli_num_rows($result) > 0){
		foreach (mysqli_fetch_all($result, MYSQLI_ASSOC) as $row){
			$row["campaign_data"] = json_decode($row["campaign_data"]);	//avoid double json encoding
			$row['date'] = getInClientTime_FD($DTime_info,$row['date'],null,'d-m-Y h:i A');
			$row['scheduled_time'] = getInClientTime_FD($DTime_info,$row['scheduled_time'],null,'d-m-Y h:i A');
			$row['stop_time'] = getInClientTime_FD($DTime_info,$row['stop_time'],null,'d-m-Y h:i A');
        	array_push($resp,$row);
		}
		echo json_encode($resp, JSON_INVALID_UTF8_IGNORE);
	}
	else
		echo json_encode(['error' => 'No data']);	
}

function getCampaignFromCampaignListId($conn, $campaign_id){
	$sent_failed_count = $sent_success_count = 0;
	$resp=$live_mcamp_data=[];
	$timestamp_conv = [];
	$DTime_info = getTimeInfo($conn);
	$resp['timezone'] = $DTime_info['time_zone']['timezone'];

	$stmt = $conn->prepare("SELECT sending_status FROM tb_data_mailcamp_live WHERE campaign_id=?");
	$stmt->bind_param("s", $campaign_id);
	$stmt->execute();
	$result = $stmt->get_result();
	$rows = $result->fetch_all();
	foreach ($rows as $row){ 
		if($row[0] == 2)
			$sent_success_count++;
		elseif($row[0] == 3)
			$sent_failed_count++;
	}
	$live_mcamp_data['sent_success_count']=$sent_success_count;
	$live_mcamp_data['sent_failed_count']=$sent_failed_count;
	$resp['live_mcamp_data'] = $live_mcamp_data;
	$resp['live_mcamp_data']['scatter_data']=[];

	$scatter_data_mail_full=getTimelineDataMail($conn, $campaign_id, $DTime_info);
	$resp['live_mcamp_data']['scatter_data'] = $scatter_data_mail_full['scatter_data_mail'];
	$resp['live_mcamp_data']['mail_open_count'] = $scatter_data_mail_full['mail_open_count'];
	$resp['live_mcamp_data']['timestamp_conv'] = $scatter_data_mail_full['timestamp_conv'];

	//-------------------
	$stmt = $conn->prepare("SELECT campaign_name,campaign_data,date,scheduled_time,camp_status FROM tb_core_mailcamp_list WHERE campaign_id = ?");
	$stmt->bind_param("s", $campaign_id);
	$stmt->execute();
	$result = $stmt->get_result();
	if($row = $result->fetch_assoc()){
		$resp['campaign_name'] = $row['campaign_name'];
		$resp['campaign_data'] = json_decode($row["campaign_data"]);//avoid double json encoding
		$resp['date'] = getInClientTime_FD($DTime_info,$row['date'],null,'d-m-Y h:i A');
		$resp['scheduled_time'] = getInClientTime_FD($DTime_info,$row['scheduled_time'],null,'d-m-Y h:i A');
		$resp['camp_status'] = $row['camp_status'];
		echo json_encode($resp, JSON_INVALID_UTF8_IGNORE);
	}
	else
		echo json_encode(['error' => 'No data']);	
	$stmt->close();
}

function deleteMailCampaignFromCampaignId($conn,$campaign_id){	
	$stmt = $conn->prepare("DELETE FROM tb_core_mailcamp_list WHERE campaign_id = ?");
	$stmt->bind_param("s", $campaign_id);
	$stmt->execute();
	if($stmt->affected_rows != 0){
		echo json_encode(['result' => 'success']);	
		deleteLiveMailcampData($conn,$campaign_id); // Clear live data before starting or when campaign deletes
	}
	else
		echo json_encode(['result' => 'failed', 'error' => $stmt->error]);	
	$stmt->close();
}

function makeCopyMailCampaignList($conn, $old_campaign_id, $new_campaign_id, $new_campaign_name){
	$stmt = $conn->prepare("INSERT INTO tb_core_mailcamp_list (campaign_id,campaign_name,campaign_data,date,scheduled_time,camp_status) SELECT ?, ?, campaign_data,?,scheduled_time,0 FROM tb_core_mailcamp_list WHERE campaign_id=?");
	$stmt->bind_param("ssss", $new_campaign_id, $new_campaign_name, $GLOBALS['entry_time'], $old_campaign_id);
	
	if ($stmt->execute() === TRUE){
		echo json_encode(['result' => 'success']);	
	}
	else 
		echo json_encode(['result' => 'failed', 'error' => $stmt->error]);	
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

	echo json_encode($resp, JSON_INVALID_UTF8_IGNORE);
}

function startStopMailCampaign($conn, $campaign_id, $action_value){	
	if($action_value == 3)
		$stop_time = $GLOBALS['entry_time'];
	else
		$stop_time = null;

	$stmt = $conn->prepare("UPDATE tb_core_mailcamp_list SET camp_status=?,stop_time=? where campaign_id=?");
	$stmt->bind_param('sss', $action_value,$stop_time,$campaign_id);
	if ($stmt->execute() === TRUE)
		echo json_encode(['result' => 'success']);	
	else 
		echo json_encode(['result' => 'failed', 'error' => $stmt->error]);	

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
	$DTime_info = getTimeInfo($conn);
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
			$row['date'] = getInClientTime_FD($DTime_info,$row['date']);
			echo json_encode($row) ;
		}		
		else
			echo json_encode(['error' => 'No data']);	
	}
	else
		echo json_encode(['error' => 'No data']);	
	$stmt->close();
}

function multi_get_mcampinfo_from_mcamp_list_id_get_live_mcamp_data($conn, $POSTJ){
	$offset = htmlspecialchars($POSTJ['start']);
	$limit = htmlspecialchars($POSTJ['length']);
	$draw = htmlspecialchars($POSTJ['draw']);
	$search_value = htmlspecialchars($POSTJ['search']['value']);
	$data = array();
	$columnIndex = htmlspecialchars($POSTJ['order'][0]['column']); // Column index
	$columnName = $POSTJ['columns'][$columnIndex]['data']; // Column name, regex removes non-alphanumeric
	$columnSortOrder = $POSTJ['order'][0]['dir'] == 'asc'?'asc':'desc'; // asc or desc
	$totalRecords = 0;
	$campaign_id = $POSTJ['campaign_id'];
	$selected_col = $POSTJ['selected_col'];
	$tb_data_single = $POSTJ['tb_data_single'];
	$arr_filtered = [];
	$DTime_info = getTimeInfo($conn);

	if (!in_array($columnName, ['rid','sending_status','send_time','user_name','user_email','send_error','mail_open_times','public_ip','ip_info','user_agent','mail_client','platform','device_type','all_headers']))	//should be db column name
	    $columnName = '';	
	if($columnName == '')
		$colSortString = '';
	else
		$colSortString = 'ORDER BY '.$columnName.' '.$columnSortOrder;

	$stmt = $conn->prepare("SELECT COUNT(*) FROM tb_data_mailcamp_live WHERE campaign_id=?");
	$stmt->bind_param("s", $tracker_id);
	$stmt->execute();
	$row = $stmt->get_result()->fetch_row();
	$totalRecords = $row[0];
	$totalRecords_with_filter = $totalRecords;//will be updated from below

	$stmt = $conn->prepare("SELECT * FROM tb_data_mailcamp_live WHERE campaign_id=? ".$colSortString." LIMIT ? OFFSET ?");
	$stmt->bind_param("sss", $campaign_id,$limit,$offset);
	$stmt->execute();
	$result = $stmt->get_result();
	$rows = $result->fetch_all(MYSQLI_ASSOC);
	foreach($rows as $i => $row){
		$tmp = [];
		$ip_info = json_decode($row['ip_info'],true);

		foreach ($selected_col as $col){ 
			if($col=='mail_open'||$col=='mail_open_times'||$col=='mail_open_count'||$col=='mail_first_open'||$col=='mail_last_open'){
	    		if($col=='mail_open')
	    			$tmp[$col] = count((array)$row['mail_open_times'])>0?true:false;
	    		elseif($col=='mail_open_count')
	    			$tmp[$col] = count((array)$row['mail_open_times']);
	    		elseif($col=='mail_open_times'){
		    		$tmp[$col] = [];
		    		foreach (json_decode($row['mail_open_times']) as $open_time)
		    			array_push($tmp[$col],getInClientTime($DTime_info,$open_time));
		    	}
		    	elseif($col=='mail_first_open' && count((array)$row['mail_open_times']) >0)
		    		$tmp[$col] = getInClientTime($DTime_info,json_decode($row['mail_open_times'],true)[0]);
		    	elseif($col=='mail_last_open' && count((array)$row['mail_open_times']) > 0)
		    		$tmp[$col] = getInClientTime($DTime_info,json_decode($row['mail_open_times'],true)[count((array)$row['mail_open_times'])-1]);
		    	else
		    		$tmp[$col]=null;
		    }
		    elseif($col=='public_ip'||$col=='user_agent'||$col=='mail_client'||$col=='platform'||$col=='device_type'||$col=='all_headers'){
		    	if($tb_data_single == true)
		    		$tmp[$col] = json_decode($row[$col],true)[0];
		    	else
		    		$tmp[$col] = json_decode($row[$col],true);
		    }
		    elseif($col=='send_time')
				$tmp[$col] = getInClientTime($DTime_info,$row[$col]);	
			elseif(array_key_exists($col,$row))
				$tmp[$col] = $row[$col];	    
	    	elseif(array_key_exists($col,(array)$ip_info))
	    		$tmp[$col] = $ip_info[$col];
	    	else
	    		$tmp[$col] = null;

	    	if(!empty($search_value)){
		    	if(is_array($tmp[$col]))
		    		$d_string = implode($tmp[$col]);
		    	else
		    		$d_string = $tmp[$col];

		    	if(stripos($d_string, $search_value) !== false)
		    		$f_found = true;
		    }		    
		}
		if(!empty($tmp))
			if(empty($search_value) || (!empty($search_value) && $f_found == true))
				array_push($arr_filtered,$tmp);	
	}

	$totalRecords_with_filter = sizeof($arr_filtered);
	$stmt->close();
	$resp = array(
		  "draw" => intval($draw),
		  "recordsTotal" => intval($totalRecords),
		  "recordsFiltered" => intval($totalRecords_with_filter),
		  "data" => $arr_filtered
		);

	echo json_encode($resp, JSON_INVALID_UTF8_IGNORE);
}

function downloadReport($conn,$campaign_id,$selected_col,$dic_all_col,$file_name,$file_format,$tb_data_single){
	$arr_odata=[];
	$DTime_info = getTimeInfo($conn);

	if(in_array('mail_reply',$selected_col) || in_array('mail_reply_count',$selected_col) || in_array('mail_reply_content',$selected_col))
		$arr_reply_emails = getMailReplied($conn, $campaign_id, true);
	else
		$arr_reply_emails=[];

	$stmt = $conn->prepare("SELECT * FROM tb_data_mailcamp_live WHERE campaign_id=?");
	$stmt->bind_param("s", $campaign_id);

	$stmt->execute();
	$result = $stmt->get_result();
	$rows = $result->fetch_all(MYSQLI_ASSOC);
	foreach($rows as $i => $row){
		$tmp = [];
		$ip_info = json_decode($row['ip_info'],true);
		$f_found = false;

		foreach ($selected_col as $col){ 
			if($col=='mail_open'||$col=='mail_open_times'||$col=='mail_open_count'||$col=='mail_first_open'||$col=='mail_last_open'){
		    		if($col=='mail_open')
		    			$tmp[$col] = count((array)$row['mail_open_times'])>0?'Yes':'No';
		    		elseif($col=='mail_open_count')
		    			$tmp[$col] = count((array)$row['mail_open_times']);
		    		elseif($col=='mail_open_times'){
			    		$tmp[$col] = [];
			    		foreach (json_decode($row['mail_open_times']) as $open_time)
			    			array_push($tmp[$col],getInClientTime($DTime_info,$open_time));
			    		$tmp[$col] = implode( ',', $tmp[$col]);
			    	}
			    	elseif($col=='mail_first_open' && count((array)$row['mail_open_times']) >0)
			    		$tmp[$col] = getInClientTime($DTime_info,json_decode($row['mail_open_times'],true)[0]);
			    	elseif($col=='mail_last_open' && count((array)$row['mail_open_times']) > 0)
			    		$tmp[$col] = getInClientTime($DTime_info,json_decode($row['mail_open_times'],true)[count((array)$row['mail_open_times'])-1]);
			    	else
			    		$tmp[$col]=null;
		    }
		    elseif($col=='public_ip'||$col=='user_agent'||$col=='mail_client'||$col=='platform'||$col=='device_type'||$col=='all_headers'){
		    	if($tb_data_single == true)
		    		$tmp[$col] = json_decode($row[$col],true)[0];
		    	else
		    		$tmp[$col] = implode( ',', json_decode($row[$col],true));	
		    }
		    elseif($col=='send_time')
				$tmp[$col] = getInClientTime($DTime_info,$row[$col]);
			elseif(array_key_exists($col,$row))
				$tmp[$col] = $row[$col];	    
	    	elseif(array_key_exists($col,(array)$ip_info))
	    		$tmp[$col] = $ip_info[$col];
	    	elseif($col=='mail_reply' || $col=='mail_reply_count' || $col=='mail_reply_content'){
	    		if(!empty($arr_reply_emails['msg_info'][$row['user_email']])){
	    			if($col=='mail_reply')
	    				$tmp['mail_reply'] = 'Yes';
	    			elseif($col=='mail_reply_content')
	    				$tmp['mail_reply_content'] =  $tb_data_single==true?$arr_reply_emails['msg_info'][$row['user_email']]['msg_body'][0]:$arr_reply_emails['msg_info'][$row['user_email']]['msg_body'];
	    			else
	    				$tmp['mail_reply_count'] = count((array)$arr_reply_emails['msg_info'][$row['user_email']]['msg_body']);
	    		}
	    		else{
	    			if($col=='mail_reply')
	    				$tmp['mail_reply'] = 'No';
	    			elseif($col=='mail_reply_content')
	    				$tmp['mail_reply_content'] =  null;
	    			else
	    				$tmp['mail_reply_count'] = 0;
	    		}
	    	}
	    	else
	    		$tmp[$col] = null;	    	
		}
		if(!empty($tmp))
			array_push($arr_odata,$tmp);			
	}

	foreach ($arr_odata as $i => $line){ 
		foreach ($line as $col=> $item){
			if(is_array($item))
				$arr_odata[$i][$col]= implode(",",$item);

			if($col == 'sending_status'){
				if($arr_odata[$i]['sending_status'] == 1)
					$arr_odata[$i]['sending_status'] = 'In-progress';
				elseif($arr_odata[$i]['sending_status'] == 2)
					$arr_odata[$i]['sending_status'] = 'Send success';
				elseif($arr_odata[$i]['sending_status'] == 3)
					$arr_odata[$i]['sending_status'] = 'Send error';
			}

			if(!in_array($col,$selected_col))	//this line should be last in loop
				unset($arr_odata[$i][$col]);
		}
	}

	if($file_format == 'csv'){
		$f = fopen('php://memory', 'w'); 

		$tmp=[];
		foreach ($selected_col as $col)
			if(array_key_exists($col,$dic_all_col))
				array_push($tmp,$dic_all_col[$col]);
			else
				array_push($tmp,$col);
		
		fputcsv($f, $tmp);

		foreach ($arr_odata as $line) 
			fputcsv($f, $line, ',');
		fseek($f, 0);
	    header('Content-Type: text/csv');
	    header('Content-Disposition: attachment;filename="'.$file_name.'.csv"');
	    fpassthru($f);
	}
	elseif ($file_format == 'pdf') {
		$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
		$pdf->SetCreator(PDF_CREATOR);
		$pdf->SetAuthor('SniperPhish');
		$pdf->SetTitle('Report data');
		$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
		$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
		$pdf->SetFont('helvetica', '', 8, '', true);
		$pdf->AddPage();

		$html_data=getHTMLData($arr_odata,$file_name,$selected_col,$dic_all_col);

		$pdf->writeHTML($html_data, true, false, true, false, '');
		$pdf->lastPage();
		$pdf->Output($file_name.'.pdf', 'I');
	}
	elseif ($file_format == 'html') {
		header('Content-Type: text/html');
	    header('Content-Disposition: attachment;filename="'.$file_name.'.html"');
		echo getHTMLData($arr_odata,$file_name,$selected_col,$dic_all_col);
	}
	
}
?>