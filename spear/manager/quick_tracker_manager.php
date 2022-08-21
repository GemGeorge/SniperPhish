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
		if($POSTJ['action_type'] == "save_quick_tracker")
			saveQuickTracker($conn,$POSTJ);
		if($POSTJ['action_type'] == "get_quick_tracker_list")
			getQuickTrackerList($conn);
		if($POSTJ['action_type'] == "delete_quick_tracker")
			deleteQuickTracker($conn, $POSTJ['tracker_id']);
		if($POSTJ['action_type'] == "delete_quick_tracker_data")
			deleteQuickTrackerData($conn, $POSTJ['tracker_id']);
		if($POSTJ['action_type'] == "pause_stop_quick_tracker_tracking")
			pauseStopQuickTrackerTracking($conn, $POSTJ['tracker_id'], $POSTJ['active']);
	    
		if($POSTJ['action_type'] == "get_quick_tracker_from_id")
			getQuickTrackerFromId($conn,$POSTJ['tracker_id']);
		if($POSTJ['action_type'] == "get_quick_tracker_data")
			getQuickTrackerData($conn,$POSTJ);
		if($POSTJ['action_type'] == "download_report")
			downloadReport($conn, $POSTJ['tracker_id'],$POSTJ['selected_col'],$POSTJ['dic_all_col'],$POSTJ['file_name'],$POSTJ['file_format'],$POSTJ['tb_data_single']);
	}
}

//-----------------------------
function saveQuickTracker($conn, &$POSTJ) { 
	$tracker_name = $POSTJ['quick_tracker_name'];
	$tracker_id = $POSTJ['tracker_id'];

	if(checkAnIDExist($conn,$tracker_id,'tracker_id','tb_core_quick_tracker_list')){
		$stmt = $conn->prepare("UPDATE tb_core_quick_tracker_list SET tracker_name = ?, date =? WHERE tracker_id=?");
		$stmt->bind_param('sss', $tracker_name,$GLOBALS['entry_time'], $tracker_id);
	}
	else{
		$stmt = $conn->prepare("INSERT INTO tb_core_quick_tracker_list(tracker_id,tracker_name,date) VALUES(?,?,?)");
		$stmt->bind_param('sss', $tracker_id,$tracker_name,$GLOBALS['entry_time']);
	}
	if ($stmt->execute() === TRUE)
		echo json_encode(['result' => 'success']);	
	else 
		echo json_encode(['result' => 'failed', 'error' => 'Error saving data']);	
}

function getQuickTrackerList($conn){	
	$DTime_info = getTimeInfo($conn);
	$resp = [];

	$result = mysqli_query($conn, "SELECT tracker_id,tracker_name,date,start_time,stop_time,active FROM tb_core_quick_tracker_list");
	if(mysqli_num_rows($result) > 0){
		foreach (mysqli_fetch_all($result, MYSQLI_ASSOC) as $row){
			$row['date'] = getInClientTime_FD($DTime_info,$row['date'],null,'d-m-Y h:i A');
			$row['start_time'] = getInClientTime_FD($DTime_info,$row['start_time'],null,'d-m-Y h:i A');
			$row['stop_time'] = getInClientTime_FD($DTime_info,$row['stop_time'],null,'d-m-Y h:i A');
        	array_push($resp,$row);
		}
		echo json_encode($resp, JSON_INVALID_UTF8_IGNORE);
	}
	else
		echo json_encode(['error' => 'No data']);
}

function deleteQuickTracker($conn, $tracker_id){	
	$stmt = $conn->prepare("DELETE FROM tb_core_quick_tracker_list WHERE tracker_id = ?");
	$stmt->bind_param("s", $tracker_id);
	$stmt->execute();

	if($stmt->affected_rows != 0)
		deleteQuickTrackerData($conn, $tracker_id);
	else
		echo json_encode(['result' => 'failed', 'error' => 'Quick tracker id does not exist']);	
	$stmt->close();	
}

function pauseStopQuickTrackerTracking($conn, $tracker_id, $active){	
	if($active == false){ //stopping
		$stmt = $conn->prepare("UPDATE tb_core_quick_tracker_list SET active=?, stop_time=? WHERE tracker_id=?");
		$stmt->bind_param('sss', $active,$GLOBALS['entry_time'],$tracker_id);
	}
	else
		if(trackerStartedPreviously($conn,$tracker_id) == true){
			$stmt = $conn->prepare("UPDATE tb_core_quick_tracker_list SET active=?  WHERE tracker_id=?");
			$stmt->bind_param('ss', $active,$tracker_id);
		}
		else{
			$stmt = $conn->prepare("UPDATE tb_core_quick_tracker_list SET active=?, start_time=? WHERE tracker_id=?");
			$stmt->bind_param('sss', $active,$GLOBALS['entry_time'],$tracker_id);
		}

	if ($stmt->execute() === TRUE){
		echo json_encode(['result' => 'success']);	
	}
	else 
		echo json_encode(['result' => 'failed', 'error' => 'Error changing status']);	
}

function deleteQuickTrackerData($conn, $tracker_id){
	$stmt = $conn->prepare("DELETE FROM tb_data_quick_tracker_live WHERE tracker_id = ?");
	$stmt->bind_param("s", $tracker_id);
	
	if ($stmt->execute() === TRUE)
		echo(json_encode(['result' => 'success']));	
	else 
		echo(json_encode(['result' => 'failed', 'error' => 'Error deleting Quick tracker']));	
}

//---------------------------Start report section----------------
function getQuickTrackerFromId($conn,$tracker_id){
    $DTime_info = getTimeInfo($conn);
	$stmt = $conn->prepare("SELECT * FROM tb_core_quick_tracker_list WHERE tracker_id = ?");
	$stmt->bind_param("s", $tracker_id);
	$stmt->execute();
	$result = $stmt->get_result();
	if($result->num_rows != 0){
		$row = $result->fetch_assoc() ;
		$row['date'] = getInClientTime_FD($DTime_info,$row['date'],null,'d-m-Y h:i A');
		$row['start_time'] = getInClientTime_FD($DTime_info,$row['start_time'],null,'d-m-Y h:i A');
		$row['stop_time'] = getInClientTime_FD($DTime_info,$row['stop_time'],null,'d-m-Y h:i A');
		echo json_encode($row, JSON_INVALID_UTF8_IGNORE);
	}
	else
		echo json_encode(['error' => 'No data']);	
	$stmt->close();
}

function getQuickTrackerData($conn, &$POSTJ){	
	$offset = htmlspecialchars($POSTJ['start']);
	$limit = htmlspecialchars($POSTJ['length']);
	$draw = htmlspecialchars($POSTJ['draw']);
	$search_value = htmlspecialchars($POSTJ['search']['value']);
	$data = array();
	$columnIndex = htmlspecialchars($POSTJ['order'][0]['column']); // Column index
	$columnName = $POSTJ['columns'][$columnIndex]['data']; // Column name
	$columnSortOrder = $POSTJ['order'][0]['dir'] == 'asc'?'asc':'desc'; // asc or desc
	$totalRecords = 0;
	$tracker_id = $POSTJ['tracker_id'];
	$selected_col = $POSTJ['selected_col'];
	$tb_data_single = $POSTJ['tb_data_single'];
	$arr_filtered = [];
	$DTime_info = getTimeInfo($conn);

	if (!in_array($columnName, ['rid','public_ip','ip_info','user_agent','mail_client','platform','all_headers','time']))	//should be db column name
	    $columnName = '';	
	if($columnName == '')
		$colSortString = '';
	else
		$colSortString = 'ORDER BY '.$columnName.' '.$columnSortOrder;

	$stmt = $conn->prepare("SELECT COUNT(*) FROM tb_data_quick_tracker_live WHERE tracker_id=?");
	$stmt->bind_param("s", $tracker_id);
	$stmt->execute();
	$row = $stmt->get_result()->fetch_row();
	$totalRecords = $row[0];
	$totalRecords_with_filter = $totalRecords;//will be updated from below

	$stmt = $conn->prepare("SELECT * FROM tb_data_quick_tracker_live WHERE tracker_id=? ".$colSortString." LIMIT ? OFFSET ?");
	$stmt->bind_param("sss", $tracker_id,$limit,$offset);
	$stmt->execute();
	$result = $stmt->get_result();
	$rows = $result->fetch_all(MYSQLI_ASSOC);
	foreach($rows as $i => $row){
		$tmp = [];
		$ip_info = json_decode($row['ip_info'],true);
		$f_found = false;

		foreach ($selected_col as $col){
		    if($col=='time')
				$tmp[$col] = getInClientTime($DTime_info,$row[$col]);			
			elseif(array_key_exists($col,$row))
				$tmp[$col] = $row[$col];	   
	    	elseif(array_key_exists($col,$ip_info))
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

function downloadReport($conn,$tracker_id,$selected_col,$dic_all_col,$file_name,$file_format){
	$arr_odata=[];
	$DTime_info = getTimeInfo($conn);

	$stmt = $conn->prepare("SELECT * FROM tb_data_quick_tracker_live WHERE tracker_id=?");
	$stmt->bind_param("s", $tracker_id);

	$stmt->execute();
	$result = $stmt->get_result();
	if($result->num_rows != 0){		
		$rows = $result->fetch_all(MYSQLI_ASSOC);

		foreach($rows as $i => $row){
			$tmp = [];
			$ip_info = json_decode($row['ip_info'],true);

			foreach ($selected_col as $col){
			    if($col=='time')
					$tmp[$col] = getInClientTime($DTime_info,$row[$col]);			
				elseif(array_key_exists($col,$row))
					$tmp[$col] = $row[$col];	   
		    	elseif(array_key_exists($col,$ip_info))
		    		$tmp[$col] = $ip_info[$col];
		    	else
		    		$tmp[$col] = null;		    
			}
			array_push($arr_odata,$tmp);		    
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
}
//---------------------------End  report section----------------
function trackerStartedPreviously($conn,$tracker_id){
	$stmt = $conn->prepare("SELECT start_time FROM tb_core_quick_tracker_list WHERE tracker_id = ?");
	$stmt->bind_param("s", $tracker_id);
	$stmt->execute();
	$row = $stmt->get_result()->fetch_assoc();
	
	if($row['start_time'] == "")
		return false;
	else
		return true;
}
?>