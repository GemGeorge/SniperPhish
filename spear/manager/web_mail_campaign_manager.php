<?php
require_once(dirname(__FILE__,2) . '/manager/session_manager.php');
require_once(dirname(__FILE__,2) . '/libs/tcpdf_min/tcpdf.php');
//-------------------------------------------------------
date_default_timezone_set('UTC');
$entry_time = (new DateTime())->format('d-m-Y h:i A');
header('Content-Type: application/json');

if (isset($_POST)) {
	$POSTJ = json_decode(file_get_contents('php://input'),true);

	if(isset($POSTJ['action_type'])){

		if(isSessionValid() == false){
			$OPS = ['get_web_mail_tracker_from_id','get_timeline_data_web','get_webcamp_graph_data','multi_get_live_campaign_data_web_mail'];	//public permitted requests
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

		if($POSTJ['action_type'] == "get_web_mail_tracker_from_id")
			getWebMailTrackerFromId($conn, $POSTJ['campaign_id'], $POSTJ['tracker_id']);
		if($POSTJ['action_type'] == "get_campaign_list_web_mail")
			getCampaignListWebMail($conn);
		if($POSTJ['action_type'] == "get_timeline_data_web")
			getTimelineDataWeb($conn, $POSTJ['campaign_id'], $POSTJ['tracker_id'], $POSTJ['user_group_id']);
		if($POSTJ['action_type'] == "get_webcamp_graph_data")
			getWebcampGraphData($conn,  $POSTJ['campaign_id'], $POSTJ['tracker_id'], $POSTJ['user_group_id'], $POSTJ['page_count']);
		if($POSTJ['action_type'] == "multi_get_live_campaign_data_web_mail")
			multi_get_live_campaign_data_web_mail($conn, $POSTJ);
		if($POSTJ['action_type'] == "download_report")
			downloadReport($conn, $POSTJ['campaign_id'],$POSTJ['tracker_id'],$POSTJ['selected_col'],$POSTJ['dic_all_col'],$POSTJ['file_name'],$POSTJ['file_format'],$POSTJ['tb_data_single']);
	}
}

//----------------------------------------------------------------------
function getWebMailTrackerFromId($conn, $campaign_id, $tracker_id){
	$DTime_info = getTimeInfo($conn);
	$stmt = $conn->prepare("SELECT * FROM tb_core_mailcamp_list WHERE campaign_id = ?");
	$stmt->bind_param("s", $campaign_id);
	$stmt->execute();
	$result = $stmt->get_result();
	if($result->num_rows > 0){
		$row = $result->fetch_assoc();
		$row['campaign_data'] = json_decode($row["campaign_data"]);//avoid double json encoding
		$row['date'] = getInClientTime_FD($DTime_info,$row['date']);
		$row['scheduled_time'] = getInClientTime_FD($DTime_info,$row['scheduled_time']);
		$row['stop_time'] = getInClientTime_FD($DTime_info,$row['stop_time']);
		$resp['mailcamp_info'] = $row;
	}

	$stmt = $conn->prepare("SELECT tracker_name,tracker_step_data,date,start_time,stop_time,active FROM tb_core_web_tracker_list WHERE tracker_id = ?");
	$stmt->bind_param("s", $tracker_id);
	$stmt->execute();
	$result = $stmt->get_result();
	if($result->num_rows > 0){
		$row = $result->fetch_assoc();
		$row['tracker_step_data'] = json_decode($row["tracker_step_data"]);	
		$row['date'] = getInClientTime_FD($DTime_info,$row['date']);
		$row['start_time'] = getInClientTime_FD($DTime_info,$row['start_time']);
		$row['stop_time'] = getInClientTime_FD($DTime_info,$row['stop_time']);
		$resp['webtracker_info'] = $row;
	}	
	echo json_encode($resp, JSON_INVALID_UTF8_IGNORE);
	$stmt->close();	
}

function getCampaignListWebMail($conn){
	$resp = [];
	$DTime_info = getTimeInfo($conn);

	$result = mysqli_query($conn, "SELECT campaign_id,campaign_name,campaign_data,date,scheduled_time,camp_status FROM tb_core_mailcamp_list");
	if(mysqli_num_rows($result) > 0){
		foreach (mysqli_fetch_all($result, MYSQLI_ASSOC) as $row){
			$row["campaign_data"] = json_decode($row["campaign_data"]);	
			$row['date'] = getInClientTime_FD($DTime_info,$row['date'],null,'d-m-Y h:i A');
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
			$row['date'] = getInClientTime_FD($DTime_info,$row['date'],null,'d-m-Y h:i A');
			$row['start_time'] = getInClientTime_FD($DTime_info,$row['start_time'],null,'d-m-Y h:i A');
			$row['stop_time'] = getInClientTime_FD($DTime_info,$row['stop_time'],null,'d-m-Y h:i A');
        	array_push($resp,$row);
		}
		$campaign['webtracker_list'] = $resp;
	}
	else
		$campaign['webtracker_list'] = ['error' => 'No data'];

	echo json_encode($campaign, JSON_INVALID_UTF8_IGNORE);
}

function getTimelineDataWeb($conn, $campaign_id, $tracker_id, $user_group_id){
	$rid_arr = $rid_pv = $rid_fs = $timestamp_conv_web=[];
	$scatter_data_web=['pv'=>[], 'fs'=>[]];
	$DTime_info = getTimeInfo($conn);
	
	$stmt = $conn->prepare("SELECT user_data FROM tb_core_mailcamp_user_group WHERE user_group_id = ?");
	$stmt->bind_param("s", $user_group_id);
	$stmt->execute();
	$result = $stmt->get_result();
	if($result->num_rows > 0)
		$user_data = json_decode($result->fetch_assoc()['user_data']);


	$stmt = $conn->prepare("SELECT rid,time FROM tb_data_webpage_visit WHERE tracker_id = ?");
	$stmt->bind_param("s", $tracker_id);
	$stmt->execute();
	$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
	foreach($rows as $row)
		array_push($rid_pv,$row);

	$stmt = $conn->prepare("SELECT rid,time,page FROM tb_data_webform_submit WHERE tracker_id = ?");
	$stmt->bind_param("s", $tracker_id);
	$stmt->execute();
	$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
	foreach($rows as $row)
		array_push($rid_fs,$row);

	$stmt = $conn->prepare("SELECT rid,user_name,user_email FROM tb_data_mailcamp_live WHERE campaign_id=?");
	$stmt->bind_param("s", $campaign_id);
	$stmt->execute();
	$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
	foreach($rows as $row)
		$rid_arr[$row['rid']] = $row;

	foreach($rid_pv as $rid_time){
		if(array_key_exists($rid_time['rid'], $rid_arr)){			
			array_push($scatter_data_web['pv'],[$rid_time['rid']=>['time'=>$rid_time['time'], 'user_name'=>$rid_arr[$rid_time['rid']]['user_name'], 'user_email'=>$rid_arr[$rid_time['rid']]['user_email']]]);
			$timestamp_conv_web[$rid_time['time']] = getInClientTime($DTime_info,$rid_time['time']);
		}
	}

	foreach($rid_fs as $rid_time){
		if(array_key_exists($rid_time['rid'], $rid_arr)){			
			array_push($scatter_data_web['fs'],[$rid_time['rid']=>['time'=>$rid_time['time'], 'user_name'=>$rid_arr[$rid_time['rid']]['user_name'], 'user_email'=>$rid_arr[$rid_time['rid']]['user_email']]]);
			$timestamp_conv_web[$rid_time['time']] = getInClientTime($DTime_info,$rid_time['time']);
		}
	}

	$scatter_data_mail_full=getTimelineDataMail($conn, $campaign_id, $DTime_info);
	$scatter_data_mail = $scatter_data_mail_full['scatter_data_mail'];
	$timestamp_conv_mail = $scatter_data_mail_full['timestamp_conv'];
	$timestamp_conv = $timestamp_conv_mail+$timestamp_conv_web;
	
	echo json_encode(['scatter_data_mail'=>$scatter_data_mail, 'scatter_data_web'=>$scatter_data_web, 'timestamp_conv'=>$timestamp_conv, 'timezone'=>$DTime_info['time_zone']['timezone']], JSON_INVALID_UTF8_IGNORE);
}

function getWebcampGraphData($conn, $campaign_id, $tracker_id, $user_group_id, $page_count){
	$rid_arr = $rid_pv = $rid_fs = $arr_fs_count = [];
	$arr_rids=['pv'=>[], 'fs'=>[], 'suspect_pv'=>[], 'suspect_fs'=>[]];
	$stmt = $conn->prepare("SELECT user_data FROM tb_core_mailcamp_user_group WHERE user_group_id = ?");
	$stmt->bind_param("s", $user_group_id);
	$stmt->execute();
	$result = $stmt->get_result();
	if($result->num_rows > 0)
		$total_user_count = count((array)json_decode($result->fetch_assoc()['user_data']));
	else
		$total_user_count = 0;

	$stmt = $conn->prepare("SELECT rid FROM tb_data_webpage_visit WHERE tracker_id = ?");
	$stmt->bind_param("s", $tracker_id);
	$stmt->execute();
	$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
	foreach($rows as $row)
		array_push($rid_pv,$row['rid']);

	$stmt = $conn->prepare("SELECT rid,page FROM tb_data_webform_submit WHERE tracker_id = ?");
	$stmt->bind_param("s", $tracker_id);
	$stmt->execute();
	$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
	foreach($rows as $row)
		array_push($rid_fs,['rid'=>$row['rid'], 'page'=>$row['page']]);

	$stmt = $conn->prepare("SELECT rid FROM tb_data_mailcamp_live WHERE campaign_id = ?");
	$stmt->bind_param("s", $campaign_id);
	$stmt->execute();
	$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
	foreach($rows as $row)
		array_push($rid_arr,$row['rid']);

	foreach($rid_pv as $rid)
		if(in_array($rid, $rid_arr)){
			if(!in_array($rid,$arr_rids['pv']))
				array_push($arr_rids['pv'],$rid);
		}
		else{
			if(!in_array($rid,$arr_rids['suspect_pv']))
				array_push($arr_rids['suspect_pv'],$rid);
		}

	foreach($rid_fs as $rid_page)
		if(in_array($rid_page['rid'], $rid_arr)){
			if(!isset($arr_rids['fs'][$rid_page['page']]))	//create array with page num as index
				$arr_rids['fs'][$rid_page['page']]=[];

			$tmp_arr = ['rid'=>$rid_page['rid'], 'page'=>$rid_page['page']];
			if(!in_array($rid_page['rid'],$arr_rids['fs'][$rid_page['page']])){				
				array_push($arr_rids['fs'][$rid_page['page']],$rid_page['rid']);
			}
		}
		else
			if(!in_array($rid_page['rid'],$arr_rids['suspect_fs']))
				array_push($arr_rids['suspect_fs'],$rid_page['rid']);
			
	$total_pv = count($arr_rids['pv']);
	$total_fs = count($arr_rids['fs']);
	$total_suspect_pv = count($arr_rids['suspect_pv']);
	$total_suspect_fs = count($arr_rids['suspect_fs']);

	foreach($arr_rids['fs'] as $i => $rid)
		$arr_fs_count[$i] = count($rid);

	echo json_encode(['total_user_count'=>$total_user_count, 'total_pv'=>$total_pv, 'total_fs'=>$total_fs, 'fs_counts'=>$arr_fs_count, 'total_suspect_pv'=>$total_suspect_pv, 'total_suspect_fs'=>$total_suspect_fs], JSON_INVALID_UTF8_IGNORE);
}


function multi_get_live_campaign_data_web_mail($conn, $POSTJ){
	$offset = htmlspecialchars($POSTJ['start']);
	$limit = htmlspecialchars($POSTJ['length']);
	$draw = htmlspecialchars($POSTJ['draw']);
	$search_value = htmlspecialchars($POSTJ['search']['value']);
	$columnIndex = htmlspecialchars($POSTJ['order'][0]['column']); // Column index
	$columnName = $POSTJ['columns'][$columnIndex]['data']; // Column name, regex removes non-alphanumeric
	$columnSortOrder = $POSTJ['order'][0]['dir'] == 'asc'?'asc':'desc'; // asc or desc
	$totalRecords = 0;
	$campaign_id = $POSTJ['campaign_id'];
	$tracker_id = $POSTJ['tracker_id'];
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

	//--------
	$campaign['webtracker_live']['page_visit'] = [];
	$stmt = $conn->prepare("SELECT * FROM tb_data_webpage_visit WHERE tracker_id=?");
	$stmt->bind_param("s", $tracker_id);
	$stmt->execute();
	$result = $stmt->get_result();
	$rows = $result->fetch_all(MYSQLI_ASSOC);
	foreach($rows as $i => $row){
		$row['ip_info'] = json_decode($row['ip_info'],true);		
		array_push($campaign['webtracker_live']['page_visit'],$row);
	}

	$campaign['webtracker_live']['form_submit'] =[];
	$stmt = $conn->prepare("SELECT * FROM tb_data_webform_submit WHERE tracker_id=?");
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
	//-------

	$stmt = $conn->prepare("SELECT COUNT(*) FROM tb_data_mailcamp_live WHERE campaign_id=?");
	$stmt->bind_param("s", $campaign_id);
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
		$f_found = false;

		foreach ($selected_col as $col){ 
			if(substr($col, 0, 4) === 'wcm_'){
				$ocol=substr($col, 4);	//removes wcm_
				$arr_tmp=[];
				foreach ($campaign['webtracker_live']['page_visit'] as $hit_entry){
					if($row['rid'] == $hit_entry['rid']){
						if($tb_data_single == true){
							if($ocol=='public_ip'||$ocol=='user_agent'||$ocol=='mail_client'||$ocol=='platform'||$ocol=='device_type'||$ocol=='all_headers')
								$tmp[$col] = $hit_entry[$ocol];
							elseif(array_key_exists($ocol,$hit_entry))
								$tmp[$col] = $hit_entry[$ocol];	    
					    	elseif(array_key_exists($ocol,$hit_entry['ip_info']))
					    		$tmp[$col] = $hit_entry['ip_info'][$ocol];
					    }
					    else{
					    	if(!isset($tmp[$col]))
					    		$tmp[$col]=[];
					    	if($ocol=='public_ip'||$ocol=='user_agent'||$ocol=='mail_client'||$ocol=='platform'||$ocol=='device_type'||$ocol=='all_headers')					    		
								array_push($tmp[$col], $hit_entry[$ocol]);
							elseif(array_key_exists($ocol,$hit_entry))
								array_push($tmp[$col], $hit_entry[$ocol]);
					    	elseif(array_key_exists($ocol,$hit_entry['ip_info']))
					    		array_push($tmp[$col], $hit_entry['ip_info'][$ocol]);
					    }
					}
			 	}			 	
			}
			elseif(substr($col, 0, 4) === 'wpv_'){
				$tmp['wpv_activity'] = false;
				$tmp['wpv_visit_count'] = 0;
				$tmp['wpv_first_visit'] = null;
				$tmp['wpv_last_visit'] = null;
				$tmp['wpv_visit_times'] = [];
				foreach ($campaign['webtracker_live']['page_visit'] as $hit_entry){
					if($row['rid'] == $hit_entry['rid']){
						$tmp['wpv_activity'] = true;
						$tmp['wpv_visit_count']++;
						if($tmp['wpv_first_visit'] == null)
							$tmp['wpv_first_visit'] = getInClientTime($DTime_info,$hit_entry['time']);
						$tmp['wpv_last_visit'] = getInClientTime($DTime_info,$hit_entry['time']);
						array_push($tmp['wpv_visit_times'],getInClientTime($DTime_info,$hit_entry['time']));
					}
			 	}
			}
			elseif(substr($col, 0, 4) === 'wfs_'){
				$ocol=substr($col, 4);	//removes wfs_
				$tmp['wfs_activity'] = false;
				$tmp['wfs_submission_count'] = 0;
				$tmp['wfs_first_submission'] = null;
				$tmp['wfs_last_submission'] = null;
				$tmp['wfs_submission_times'] = [];
				foreach ($campaign['webtracker_live']['form_submit'] as $hit_entry){
					if($row['rid'] == $hit_entry['rid']){
						$tmp['wfs_activity'] = true;
						$tmp['wfs_submission_count']++;
						if($tmp['wfs_first_submission'] == null)
							$tmp['wfs_first_submission'] = getInClientTime($DTime_info,$hit_entry['time']);
						$tmp['wfs_last_submission'] = getInClientTime($DTime_info,$hit_entry['time']);
						array_push($tmp['wfs_submission_times'],getInClientTime($DTime_info,$hit_entry['time']));					
					}
			 	}
			}
			elseif(substr($col, 0, 6) === 'Field-'){
				foreach ($campaign['webtracker_live']['form_submit'] as $hit_entry){
					if($row['rid'] == $hit_entry['rid']){
						$form_field_data = $hit_entry['form_field_data'];
						$ocol=substr($col, 6);	//removes Field-
						if(!isset($tmp[$col]))	//else can overwrites with empty value
							$tmp[$col]=[];
						if($form_field_data->$ocol != null)
							array_push($tmp[$col],$form_field_data->$ocol);
						$tmp['SPPage-'.$hit_entry['page']] = true;
					}
				}
			}
			else{
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
		    }		    
		 	if(!isset($tmp[$col]))
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

function downloadReport($conn,$campaign_id,$tracker_id,$selected_col,$dic_all_col,$file_name,$file_format,$tb_data_single){
	$arr_odata=[];
	$DTime_info = getTimeInfo($conn);

	//--------
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
	//-------

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

		foreach ($selected_col as $col){ 
			if(substr($col, 0, 4) === 'wcm_'){
				$ocol=substr($col, 4);	//removes wcm_
				$arr_tmp=[];
				foreach ($campaign['webtracker_live']['page_visit'] as $hit_entry){
					if($row['rid'] == $hit_entry['rid']){
						if($tb_data_single == true){
							if($ocol=='public_ip'||$ocol=='user_agent'||$ocol=='mail_client'||$ocol=='platform'||$ocol=='device_type'||$ocol=='all_headers')
								$tmp[$col] = $hit_entry[$ocol];
							elseif(array_key_exists($ocol,$hit_entry))
								$tmp[$col] = $hit_entry[$ocol];	    
					    	elseif(array_key_exists($ocol,$hit_entry['ip_info']))
					    		$tmp[$col] = $hit_entry['ip_info'][$ocol];
					    }
					    else{
					    	if(!isset($tmp[$col]))
					    		$tmp[$col]=[];
					    	if($ocol=='public_ip'||$ocol=='user_agent'||$ocol=='mail_client'||$ocol=='platform'||$ocol=='device_type'||$ocol=='all_headers')					    		
								array_push($tmp[$col], $hit_entry[$ocol]);
							elseif(array_key_exists($ocol,$hit_entry))
								array_push($tmp[$col], $hit_entry[$ocol]);
					    	elseif(array_key_exists($ocol,$hit_entry['ip_info']))
					    		array_push($tmp[$col], $hit_entry['ip_info'][$ocol]);
					    }
					}
			 	}			 	
			}
			elseif(substr($col, 0, 4) === 'wpv_'){
				$tmp['wpv_activity'] = 'No';
				$tmp['wpv_visit_count'] = 0;
				$tmp['wpv_first_visit'] = null;
				$tmp['wpv_last_visit'] = null;
				$tmp['wpv_visit_times'] = [];
				foreach ($campaign['webtracker_live']['page_visit'] as $hit_entry){
					if($row['rid'] == $hit_entry['rid']){
						$tmp['wpv_activity'] = 'Yes';
						$tmp['wpv_visit_count']++;
						if($tmp['wpv_first_visit'] == null)
							$tmp['wpv_first_visit'] = getInClientTime($DTime_info,$hit_entry['time']);
						$tmp['wpv_last_visit'] = getInClientTime($DTime_info,$hit_entry['time']);
						array_push($tmp['wpv_visit_times'],getInClientTime($DTime_info,$hit_entry['time']));
						
					}
			 	}
			}
			elseif(substr($col, 0, 4) === 'wfs_'){
				$ocol=substr($col, 4);	//removes wfs_
				$tmp['wfs_activity'] = 'No';
				$tmp['wfs_submission_count'] = 0;
				$tmp['wfs_first_submission'] = null;
				$tmp['wfs_last_submission'] = null;
				$tmp['wfs_submission_times'] = [];
				foreach ($campaign['webtracker_live']['form_submit'] as $hit_entry){
					if($row['rid'] == $hit_entry['rid']){
						$tmp['wfs_activity'] = 'Yes';
						$tmp['wfs_submission_count']++;
						if($tmp['wfs_first_submission'] == null)
							$tmp['wfs_first_submission'] = getInClientTime($DTime_info,$hit_entry['time']);
						$tmp['wfs_last_submission'] = getInClientTime($DTime_info,$hit_entry['time']);
						array_push($tmp['wfs_submission_times'],getInClientTime($DTime_info,$hit_entry['time']));	
					}
			 	}
			}
			elseif(substr($col, 0, 6) === 'Field-'){
				foreach ($campaign['webtracker_live']['form_submit'] as $hit_entry){
					if($row['rid'] == $hit_entry['rid']){
						$form_field_data = $hit_entry['form_field_data'];
						$ocol=substr($col, 6);	//removes Field-
						if(!isset($tmp[$col]))	//else can overwrites with empty value
							$tmp[$col]=[];
						if($form_field_data->$ocol != null)
							array_push($tmp[$col],$form_field_data->$ocol);
						$tmp['SPPage-'.$hit_entry['page']] = 'Yes';
					}
				}
			}			
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
			else{
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
				    	elseif($col=='mail_first_open' && count($row['mail_open_times']) >0)
				    		$tmp[$col] = getInClientTime($DTime_info,json_decode($row['mail_open_times'],true)[0]);
				    	elseif($col=='mail_last_open' && count((array)$row['mail_open_times']) > 0)
				    		$tmp[$col] = getInClientTime($DTime_info,json_decode($row['mail_open_times'],true)[count((array)$row['mail_open_times'])-1]);
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
		    }		    
		 	if(!isset($tmp[$col]))
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

			if(substr($col, 0, 7) === 'SPPage-'){
				if($arr_odata[$i][$col] == true)
					$arr_odata[$i][$col] = 'Yes';
				else
					$arr_odata[$i][$col] = 'No';
			}

			if(!in_array($col,$selected_col))	//this line should be last in loop
				unset($arr_odata[$i][$col]);
		}
	}

	foreach ($selected_col as $i => $col)
		if(substr($col, 0, 7) === 'SPPage-')
			$selected_col[$i] = substr($selected_col[$i], 2).' Submission';

	if($file_format == 'csv'){
		$f = fopen('php://memory', 'w'); 

		$tmp=[];
		foreach ($selected_col as $col)
			if(array_key_exists($col,$dic_all_col))
				array_push($tmp,$dic_all_col[$col]);
			else
				array_push($tmp,$col);
		
		fputcsv($f, $tmp);


		foreach ($arr_odata as $line){ 
			fputcsv($f, $line, ',');
		}
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