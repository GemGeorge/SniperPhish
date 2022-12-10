<?php
require_once(dirname(__FILE__) . '/session_manager.php');
require_once(dirname(__FILE__) . '/common_functions.php');
require_once(dirname(__FILE__,2) . '/libs/tcpdf_min/tcpdf.php');

if(isSessionValid() == false)
	die("Access denied");
//-------------------------------------------------------
date_default_timezone_set('UTC');
$entry_time = (new DateTime())->format('d-m-Y h:i A');
header('Content-Type: application/json');

if (isset($_POST)) {
	$POSTJ = json_decode(file_get_contents('php://input'),true);

	if(isset($POSTJ['action_type'])){
		if($POSTJ['action_type'] == "get_user_list")
			getUserList($conn);
		if($POSTJ['action_type'] == "add_account")
			addAccount($conn,$POSTJ['name'], $POSTJ['username'], $POSTJ['mail'], $POSTJ['dp_name'], $POSTJ['current_pwd'], $POSTJ['new_pwd']);
		if($POSTJ['action_type'] == "modify_account")
			modifyAccount($conn,$POSTJ['name'], $POSTJ['username'], $POSTJ['mail'], $POSTJ['dp_name'], $POSTJ['current_pwd'], $POSTJ['new_pwd']);
		if($POSTJ['action_type'] == "delete_account")
			deleteAccount($conn,$POSTJ['id']);
		if($POSTJ['action_type'] == "get_current_user")
			getCurrentUser($conn);

		if($POSTJ['action_type'] == "modify_timestamp_settings")
			modifyTimestampSettings($conn, json_encode($POSTJ['time_zone']), json_encode($POSTJ['time_format']));
		if($POSTJ['action_type'] == "get_timestamp_settings")
			getTimetampSettings($conn);
		if($POSTJ['action_type'] == "get_date_time_display")
			getDateTimeDisplay($conn,$POSTJ['time_zone'],$POSTJ['date_time_format']);
		if($POSTJ['action_type'] == "modify_SP_base_URL")
			modifySPBaseURL($conn, $POSTJ['baseurl']);
		if($POSTJ['action_type'] == "clear_junk_SP_data")
			clearJunkSPData($conn);

		if($POSTJ['action_type'] == "get_logs")
			getLogs($conn,$POSTJ);
		if($POSTJ['action_type'] == "download_logs")
			downloadLogs($conn,$POSTJ['file_format']);
		if($POSTJ['action_type'] == "clear_log")
			clearLog($conn);

		//Store data
		if($POSTJ['action_type'] == "get_store_list")
			getStoreList($conn, $POSTJ['type'], (isset($POSTJ['name'])?$POSTJ['name']:""));
	}
}

//-----------------------------

function getCurrentUser($conn){
	$username = $_SESSION['username'];
	$DTime_info = getTimeInfo($conn);

	$stmt = $conn->prepare("SELECT id,name,username,contact_mail,dp_name,date FROM tb_main WHERE username=?");
	$stmt->bind_param("s", $username);
	$stmt->execute();
	$result = $stmt->get_result();
	if($result->num_rows != 0){
		$row = $result->fetch_assoc() ;
		$row['date'] = getInClientTime_FD($DTime_info,$row['date'],null,'d-m-Y h:i A');
		echo json_encode($row, JSON_INVALID_UTF8_IGNORE) ;
	}
	else
		echo json_encode(['error' => 'No data']);				
	$stmt->close();
}

function getUserList($conn){
	$resp = [];
	$DTime_info = getTimeInfo($conn);
	$result = mysqli_query($conn, "SELECT id,name,username,contact_mail,dp_name,date,last_login FROM tb_main");
	if(mysqli_num_rows($result) > 0){
		foreach (mysqli_fetch_all($result, MYSQLI_ASSOC) as $row){
			$row['date'] = getInClientTime_FD($DTime_info,$row['date'],null,'d-m-Y h:i A');
			$row['last_login'] = getInClientTime_FD($DTime_info,json_decode($row['last_login'])[0],null,'d-m-Y h:i A');
        	array_push($resp,$row);
		}
		echo json_encode($resp, JSON_INVALID_UTF8_IGNORE);
	}
	else
		echo json_encode(['error' => 'No data']);
}

function addAccount($conn,$name,$username,$contact_mail,$dp_name,$current_pwd,$new_pwd){
	if(checkAnIDExist($conn,$username,'username','tb_main'))
		die(json_encode(['result' => 'failed', 'error' => 'Account with this username already exist!']));

	if(isCurrentPwdCorrect($conn,$current_pwd)){		
		$new_pwd_hash = hash("sha256", $new_pwd, false);
		$stmt = $conn->prepare("INSERT INTO tb_main(name, username, password, contact_mail, dp_name, date) VALUES(?,?,?,?,?,?)");
		$stmt->bind_param('ssssss', $name,$username,$new_pwd_hash,$contact_mail,$dp_name,$GLOBALS['entry_time']);

		if ($stmt->execute() === TRUE)
			echo json_encode(['result' => 'success']);	
		else 
			echo json_encode(['result' => 'failed', 'error' => 'Error saving data! '.$stmt->error]);	
		$stmt->close();
	}
	else
		echo(json_encode(['result' => 'failed', 'error' => 'Authorization failed! Your password is incorrect!']));
}


function modifyAccount($conn,$name,$username,$contact_mail,$dp_name,$current_pwd,$new_pwd){	
	if(isCurrentPwdCorrect($conn,$current_pwd)){	//current password is correct
		if($new_pwd == ''){	//update email only
			$stmt = $conn->prepare("UPDATE tb_main SET name=?, contact_mail=?, dp_name=? WHERE username=?");
			$stmt->bind_param('ssss', $name,$contact_mail,$dp_name,$username);
			if ($stmt->execute() === TRUE){
				echo(json_encode(['result' => 'success']));	
			}
			else 
				echo(json_encode(['result' => 'failed', 'error' => 'Contact mail update failed!']));
		}
		else{	//update all
			$new_pwd_hash = hash("sha256", $new_pwd, false);	
			$stmt = $conn->prepare("UPDATE tb_main SET name=?, password=?, contact_mail=?, dp_name=? WHERE username=?");
			$stmt->bind_param('sssss', $name,$new_pwd_hash,$contact_mail,$dp_name,$username);
			if ($stmt->execute() === TRUE)
				echo(json_encode(['result' => 'success']));	
			else 
				echo(json_encode(['result' => 'failed', 'error' => 'Update failed!']));
		}
		setInfoCookie($conn,$_SESSION['username']);	//sets c_data cookie
	}
	else
		echo(json_encode(['result' => 'failed', 'error' => 'Authorization failed! Your password is incorrect!']));
}

function isCurrentPwdCorrect(&$conn, &$current_pwd){	
	$current_pwd_hash = hash("sha256", $current_pwd, false);
	$current_username = $_SESSION['username'];

	$stmt = $conn->prepare("SELECT COUNT(*) FROM tb_main WHERE username=? AND password=?");
	$stmt->bind_param('ss', $current_username, $current_pwd_hash);
	$stmt->execute();
	$row = $stmt->get_result()->fetch_row();
	if($row[0] > 0)	//current password is correct
		return true;
	else
		return false;
}

function deleteAccount($conn,$id){
	if($id == 1)
		die(json_encode(['result' => 'failed', 'error' => 'Admin account can not be deleted!']));
	else{
		$stmt = $conn->prepare("DELETE FROM tb_main WHERE id=?");
		$stmt->bind_param("s", $id);
		if ($stmt->execute() === TRUE)
			echo(json_encode(['result' => 'success']));	
		else 
			echo(json_encode(['result' => 'failed', 'error' => 'Error deleting account!']));	
		$stmt->close();
	}
}

//----------------General Settings-----------
function getDateTimeDisplay($conn,$time_zone,$date_time_format){
	if(substr($date_time_format, 0, strlen('Unix Timestamp-seconds')) === 'Unix Timestamp-seconds')
		echo json_encode(['result' => round(microtime(true))]);
	elseif(substr($date_time_format, 0, strlen('Unix Timestamp-milliseconds')) === 'Unix Timestamp-milliseconds')
		echo json_encode(['result' => round(microtime(true) * 1000)]);
	else
		echo json_encode(['result' => getInClientTime(null,round(microtime(true) * 1000),$time_zone,$date_time_format)]);
}

function modifySPBaseURL($conn,$baseurl){
	$pieces = parse_url($baseurl);
	$server_protocol = $pieces['scheme'];
	$domain = $pieces['host'];

	$stmt = $conn->prepare("UPDATE tb_main_variables SET server_protocol=?, domain=?, baseurl=? WHERE id=1");
	$stmt->bind_param('sss', $server_protocol,$domain,$baseurl);
	if ($stmt->execute() === TRUE){
		echo(json_encode(['result' => 'success']));	
	}
	else 
		echo(json_encode(['result' => 'failed', 'error' => 'SP base URL update failed!']));
}

function modifyTimestampSettings($conn, $time_zone, $time_format){
	$stmt = $conn->prepare("UPDATE tb_main_variables SET time_zone=?, time_format =? where id=1");
	$stmt->bind_param('ss', $time_zone, $time_format);
	
	if ($stmt->execute() === TRUE)
		echo json_encode(['result' => 'success']);	
	else 
		echo json_encode(['result' => 'failed', 'error' => 'Update failed!']);	
	$stmt->close();
}

function getTimetampSettings($conn){
	$result = mysqli_query($conn, "SELECT time_zone,time_format,baseurl FROM tb_main_variables")->fetch_assoc();
	$result['time_zone'] = json_decode($result['time_zone']);
	$result['time_format'] = json_decode($result['time_format']);
	echo json_encode($result);
}

//---Clear junk data-------
//Clear junk tracker images
function clearJunkSPData(&$conn){
	try{
		clearJunkSPDataAction($conn);
		echo json_encode(['result' => 'success']);
	}
	catch(Exception $e) {
		echo json_encode(['result' => 'failed', 'error' => $e->getMessage()]);
	}
}
function clearJunkSPDataAction(&$conn){
	$mail_template_ids = $mbfs = $attachment_file_ids =  [];
	$doc = new DOMDocument();

	$result = mysqli_query($conn, "SELECT mail_template_id,mail_template_content,attachment,timage_type FROM tb_core_mailcamp_template_list")->fetch_all(MYSQLI_ASSOC);

	foreach ($result as $row) {
		if($row['timage_type'] == 2);
	    	array_push($mail_template_ids, $row['mail_template_id']);

	   	foreach (json_decode($row['attachment'],true) as $att)
	   		if(!empty($att['file_id']))
	   			array_push($attachment_file_ids, $att['file_id']);

		@$doc->loadHTML($row['mail_template_content']);
		$tags = $doc->getElementsByTagName('img');
		foreach ($tags as $tag) {
		    $src = $tag->getAttribute('src');
		    $queries = getQueryValsFromURL($src);
		    if(!empty($queries['mbf']))
		    	array_push($mbfs, $queries['mbf']);
		}
	}

	$files = glob("uploads/timages/*.timg");	//tracker images - based on tid
	foreach ($files as $file)
	  if(!in_array(basename($file,'.timg'), $mail_template_ids))
	  	unlink($file);

	$files = glob("uploads/attachments/*.att");		//usaved attachments - based on attachment ids
	foreach ($files as $file)
	  if(!in_array(basename($file,'.att'), $attachment_file_ids))
	  	unlink($file);

	$files = glob("uploads/attachments/*.mbf");		//unsaved mail body files - based on img src url with mbd parameter
	foreach ($files as $file){
	    if(!in_array(explode("_", basename($file,'.mbf'))[1], $mbfs))	//eg: if 1611333260
	  		unlink($file);
	}

	//Delete junk payload file uploads
	$pl_ids = [];
	$result = mysqli_query($conn, "SELECT pl_id FROM tb_pl_list")->fetch_all(MYSQLI_ASSOC);
	foreach ($result as $row)
	  array_push($pl_ids, $row['pl_id']);

	$files = glob("payloads/uploads/*.pdata");
	foreach ($files as $file)
	  if(!in_array(basename($file,'.pdata'), $pl_ids))
	    unlink($file);

	//Delete junk sniperhost file uploads
	$file_ids = [];
	$result = mysqli_query($conn, "SELECT hf_id FROM tb_hf_list")->fetch_all(MYSQLI_ASSOC);
	foreach ($result as $row)
	  array_push($file_ids, $row['hf_id']);

	$files = glob("sniperhost/hf_files/*.hfile");
	foreach ($files as $file)
	  if(!in_array(basename($file,'.hfile'), $file_ids))
	    unlink($file);

	//Delete junk sniperhost text uploads
	$file_ids = [];
	$result = mysqli_query($conn, "SELECT ht_id FROM tb_ht_list")->fetch_all(MYSQLI_ASSOC);
	foreach ($result as $row)
	  array_push($file_ids, $row['ht_id']);

	$files = glob("sniperhost/ht_files/*.ptdata");
	foreach ($files as $file)
	  if(!(in_array(basename($file,'_in.ptdata'), $file_ids) || in_array(basename($file,'_out.ptdata'), $file_ids)))
	    unlink($file);

	//Delete public dashboard access table entries for deleted campaigns
	$file_ids = $arr_clearList = [];
	$result = mysqli_query($conn, "SELECT campaign_id FROM tb_core_mailcamp_list")->fetch_all(MYSQLI_ASSOC);
	foreach ($result as $row)
	  array_push($file_ids, $row['campaign_id']);

	$result = mysqli_query($conn, "SELECT tracker_id FROM tb_core_web_tracker_list")->fetch_all(MYSQLI_ASSOC);
	foreach ($result as $row)
	  array_push($file_ids, $row['tracker_id']);

	$result = mysqli_query($conn, "SELECT ctrl_ids FROM tb_access_ctrl")->fetch_all(MYSQLI_ASSOC);
	foreach ($result as $row){
	  $ctrl_ids = json_decode($row['ctrl_ids']);

	  if(!in_array($ctrl_ids[0], $file_ids))
	    deleteEntry($conn,json_encode($ctrl_ids));
	  else
	  if(count($ctrl_ids)==2){
	    if(!in_array($ctrl_ids[1], $file_ids))
	      deleteEntry($conn,json_encode($ctrl_ids));
	  }
	}
}

function deleteEntry(&$conn,$ctrl_ids){
	$stmt = $conn->prepare("DELETE FROM tb_access_ctrl WHERE ctrl_ids = ?");
	$stmt->bind_param("s", $ctrl_ids);
	$stmt->execute();
	$stmt->close();
}

//---------------Store Section Start------------------------------------
function getStoreList($conn, $type, $name){
	$resp = [];

	if($type == "mail_sender"){
		$stmt = $conn->prepare("SELECT name,info,content FROM tb_store WHERE type = ?");
		$stmt->bind_param("s", $type);
		$stmt->execute();
		$result = $stmt->get_result();
		$rows = $result->fetch_all(MYSQLI_ASSOC);
		foreach($rows as $i => $row)
			$resp[$row['name']] = ["info" => json_decode($row['info']), "content" => json_decode($row['content'])];
		echo json_encode($resp, JSON_INVALID_UTF8_IGNORE);
	}

	if($type == "mail_template"){
		if(empty($name)){
			$stmt = $conn->prepare("SELECT name,info FROM tb_store WHERE type = ?");
			$stmt->bind_param("s", $type);
			$stmt->execute();
			$result = $stmt->get_result();
			$result = $result->fetch_all(MYSQLI_ASSOC);
			foreach($result as $row)
				$resp[$row['name']] = json_decode($row['info']);
			echo json_encode($resp, JSON_INVALID_UTF8_IGNORE);
		}
		else{
			$stmt = $conn->prepare("SELECT content FROM tb_store WHERE type = ? AND name = ?");
			$stmt->bind_param("ss", $type, $name);
			$stmt->execute();
			$result = $stmt->get_result();
			if($result->num_rows != 0){
				$row = $result->fetch_assoc();
				echo $row['content'];
			}
		}
	}
}

//----------------Logs-----------
function getLogs($conn, &$POSTJ){
	$offset = htmlspecialchars($POSTJ['start']);
	$limit = htmlspecialchars($POSTJ['length']);
	$draw = htmlspecialchars($POSTJ['draw']);
	$search_value = '%'.htmlspecialchars($POSTJ['search']['value']).'%';
	$data = array();
	$columnIndex = htmlspecialchars($POSTJ['order'][0]['column']); // Column index
	$columnName = $POSTJ['columns'][$columnIndex]['data']; // Column name
	$columnSortOrder = $POSTJ['order'][0]['dir'] == 'asc'?'asc':'desc'; // asc or desc
	$totalRecords = 0;
	$arr_filtered = [];
	$DTime_info = getTimeInfo($conn);

	if (!in_array($columnName, ['id','username','log','ip','date']))	//should be db column name
	    $columnName = '';	
	if($columnName == '')
		$colSortString = '';
	else
		$colSortString = 'ORDER BY '.$columnName.' '.$columnSortOrder;

	$result = mysqli_query($conn, "SELECT COUNT(*) FROM tb_log");
	$row = mysqli_fetch_row($result);
	$totalRecords = $row[0];
	$totalRecords_with_filter = $totalRecords;//will be updated from below

	if(empty($search_value)){
		$stmt = $conn->prepare("SELECT username,log,date FROM tb_log ".$colSortString." LIMIT ? OFFSET ?");
		$stmt->bind_param("ss", $limit,$offset);
	}
	else{
		$stmt = $conn->prepare("SELECT COUNT(*) FROM tb_log WHERE username LIKE ? OR log LIKE ?");
		$stmt->bind_param("ss", $search_value,$search_value);	 
		$stmt->execute();
		$result = $stmt->get_result()->fetch_row();
		$totalRecords_with_filter = $result[0];

		$stmt = $conn->prepare("SELECT username,log,ip,date FROM tb_log WHERE username LIKE ? OR log LIKE ? OR ip LIKE ? ".$colSortString." LIMIT ? OFFSET ?");
		$stmt->bind_param("sssss", $search_value,$search_value,$search_value,$limit,$offset);
	}
	$stmt->execute();
	$result = $stmt->get_result();
	$rows = $result->fetch_all(MYSQLI_ASSOC);
	
	foreach ($rows as $i => $row)
		$rows[$i]['date'] = getInClientTime_FD($DTime_info,$row['date'],null,'d-m-Y h:i A');

	$stmt->close();
	$resp = array(
		"draw" => intval($draw),
		"recordsTotal" => intval($totalRecords),
		"recordsFiltered" => intval($totalRecords_with_filter),
		"data" => $rows
	);

	echo json_encode($resp, JSON_INVALID_UTF8_IGNORE);
}

function downloadLogs($conn,$file_format){
	$arr_odata=[];
	$DTime_info = getTimeInfo($conn);
	$file_name='SPLog-'.$GLOBALS['entry_time'];
	$selected_col = ['Username','Log','IP','Date Time'];

	$result = mysqli_query($conn, "SELECT username,log,ip,date FROM tb_log");
	if(mysqli_num_rows($result) > 0){
		$arr_odata=mysqli_fetch_all($result, MYSQLI_ASSOC);
		
		foreach ($arr_odata as $i => $row)
		    $arr_odata[$i]['date'] = getInClientTime_FD($DTime_info,$row['date'],null,'d-m-Y h:i A');

		if($file_format == 'csv'){
			$f = fopen('php://memory', 'w'); 

			fputcsv($f, $selected_col);

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

			$html_data=getHTMLData($arr_odata,$file_name,$selected_col,$selected_col,false);

			$pdf->writeHTML($html_data, true, false, true, false, '');
			$pdf->lastPage();
			$pdf->Output($file_name.'.pdf', 'I');
		}
		elseif ($file_format == 'html') {
			header('Content-Type: text/html');
		    header('Content-Disposition: attachment;filename="'.$file_name.'.html"');
			echo getHTMLData($arr_odata,$file_name,$selected_col,$selected_col);
		}
	}
}

function clearLog(&$conn){
    if ($conn->query('DELETE FROM tb_log') === TRUE) 
        echo(json_encode(['result' => 'success']));	
    else
        echo json_encode(['result' => 'failed', 'error' => $conn->error]);
    
    $conn->close();
}
//-------------------------------------
?>