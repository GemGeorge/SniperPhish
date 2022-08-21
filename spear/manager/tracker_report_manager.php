<?php
require_once(dirname(__FILE__) . '/session_manager.php');
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
	    if($POSTJ['action_type'] == "get_table_webpage_visit_form_submission")
			getTableWebpageVisitFormSubmission($conn,  $POSTJ);
		if($POSTJ['action_type'] == "get_web_tracker_from_id")
			getWebTrackerFromId($conn, $POSTJ['tracker_id']);
		if($POSTJ['action_type'] == "download_report")
			downloadReport($conn, $POSTJ['tracker_id'],$POSTJ['selected_col'],$POSTJ['dic_all_col'],$POSTJ['page'],$POSTJ['file_name'],$POSTJ['file_format']);
	}
}

//---------------------
function downloadReport($conn,$tracker_id,$selected_col,$dic_all_col,$page,$file_name,$file_format){
	$arr_odata=[];
	$DTime_info = getTimeInfo($conn);

	if($page == 0){
		$stmt = $conn->prepare("SELECT * FROM tb_data_webpage_visit WHERE tracker_id=?");
		$stmt->bind_param("s", $tracker_id);
	}
	else{
		$stmt = $conn->prepare("SELECT * FROM tb_data_webform_submit WHERE tracker_id=? AND page=?");
		$stmt->bind_param("ss", $tracker_id,$page);
	}

	$stmt->execute();
	$result = $stmt->get_result();
	if($result->num_rows != 0){		
		$rows = $result->fetch_all(MYSQLI_ASSOC);

		foreach($rows as $i => $row){
			$tmp = [];
			$ip_info = json_decode($row['ip_info'],true);
			$form_field_data = json_decode($row['form_field_data'],true);

			foreach ($selected_col as $col){ 
			    if(array_key_exists($col,$row))
			    	$tmp[$col] = $row[$col];
			    else
		    	if(array_key_exists($col,$ip_info))
		    		$tmp[$col] = $ip_info[$col];
		    	else
		    	{
		    		$cust_field = str_replace("Field-",'',$col);
			    	if(array_key_exists($cust_field,$form_field_data))
			    		$tmp[$col] = $form_field_data[$cust_field];
			    	else
			    		$tmp[$col] = null;
			    }
			    if($col=='time')
			    	$tmp[$col] = getInClientTime($DTime_info,$row[$col]);
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

function getTableWebpageVisitFormSubmission($conn, &$POSTJ){
	$offset = htmlspecialchars($POSTJ['start']);
	$limit = htmlspecialchars($POSTJ['length']);
	$draw = htmlspecialchars($POSTJ['draw']);
	$search_value = htmlspecialchars($POSTJ['search']['value']);
	$data = array();
	$columnIndex = htmlspecialchars($POSTJ['order'][0]['column']); // Column index
	$columnName = $POSTJ['columns'][$columnIndex]['data']; // Column name, regex removes non-alphanumeric
	$columnSortOrder = $POSTJ['order'][0]['dir'] == 'asc'?'asc':'desc'; // asc or desc
	$totalRecords = 0;
	$tracker_id = $POSTJ['tracker_id'];
	$page = $POSTJ['page'];
	$selected_col = $POSTJ['selected_col'];
	$arr_filtered=[];
	$DTime_info = getTimeInfo($conn);

	if (!in_array($columnName, ['rid','session_id','public_ip','ip_info','user_agent','screen_res','time','browser','platform','device_type']))	//should be db column name
	    $columnName = '';
	if($columnName == '')
		$colSortString = '';
	else
		$colSortString = 'ORDER BY '.$columnName.' '.$columnSortOrder;

	if($page == 0){
		$stmt = $conn->prepare("SELECT COUNT(*) FROM tb_data_webpage_visit WHERE tracker_id=?");
		$stmt->bind_param("s", $tracker_id);
	}
	else{
		$stmt = $conn->prepare("SELECT COUNT(*) FROM tb_data_webform_submit WHERE tracker_id=? AND page=?");
		$stmt->bind_param("ss", $tracker_id,$page);
	}
	$stmt->execute();
	$row = $stmt->get_result()->fetch_row();
	$totalRecords = $row[0];
	$totalRecords_with_filter = $totalRecords;//will be updated from below

	if($page == 0){
		$stmt = $conn->prepare("SELECT * FROM tb_data_webpage_visit WHERE tracker_id=? ".$colSortString." LIMIT ? OFFSET ?");
		$stmt->bind_param("sss", $tracker_id,$limit,$offset);
	}
	else{
		$stmt = $conn->prepare("SELECT * FROM tb_data_webform_submit WHERE tracker_id=? AND page=? ".$colSortString." LIMIT ? OFFSET ?");
		$stmt->bind_param("ssss", $tracker_id,$page,$limit,$offset);
	}

	$stmt->execute();
	$result = $stmt->get_result();
	$rows = $result->fetch_all(MYSQLI_ASSOC);
	foreach($rows as $i => $row){
		$tmp = [];
		$ip_info = json_decode($row['ip_info'],true);
		$form_field_data = json_decode($row['form_field_data'],true);
		$f_found = false;

		foreach ($selected_col as $col){ 
		    if(array_key_exists($col,$row))
		    	$tmp[$col] = $row[$col];
		    else
	    	if(array_key_exists($col,$ip_info))
	    		$tmp[$col] = $ip_info[$col];
	    	else
	    	{
	    		$cust_field = str_replace("Field-",'',$col);
		    	if(array_key_exists($cust_field,$form_field_data))
		    		$tmp[$col] = $form_field_data[$cust_field];
		    	else
		    		$tmp[$col] = null;
		    }
		    if($col=='time')
				$tmp[$col] = getInClientTime($DTime_info,$row[$col]);

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

function getWebTrackerFromId($conn, $tracker_id){	
	$stmt = $conn->prepare("SELECT * FROM tb_core_web_tracker_list WHERE tracker_id = ?");
	$stmt->bind_param("s", $tracker_id);
	$stmt->execute();
	$result = $stmt->get_result();
	if($result->num_rows > 0){
		$row = $result->fetch_assoc();
		$row['tracker_step_data'] = json_decode($row["tracker_step_data"]);	
		echo json_encode($row, JSON_INVALID_UTF8_IGNORE) ;
	}
	else
		echo json_encode(['error' => 'No data']);
	$stmt->close();	
}
?>