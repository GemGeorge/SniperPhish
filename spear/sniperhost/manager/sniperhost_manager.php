<?php
//-------------------Session check-----------------------
require_once(dirname(__FILE__,3) . '/config/db.php');
require_once(dirname(__FILE__,3) . '/manager/session_manager.php');
require_once(dirname(__FILE__,3) . '/manager/common_functions.php');
require_once(dirname(__FILE__,2) . '/lib/Base32.php');
require_once(dirname(__FILE__,2) . '/lib/base85.class.php');
if(isSessionValid() == false)
	die("Access denied");
//-------------------------------------------------------
use Base32\Base32;
date_default_timezone_set('UTC');
$entry_time = (new DateTime())->format('d-m-Y h:i A');
header('Content-Type: application/json');

if (isset($_POST)) {
	$POSTJ = json_decode(file_get_contents('php://input'),true);

	if(isset($POSTJ['action_type'])){
		if($POSTJ['action_type'] == "get_result_alg")
			getResultAlg(false, $POSTJ);
		if($POSTJ['action_type'] == "save_plaintext")
			savePlaintext($conn, $POSTJ);
		if($POSTJ['action_type'] == "get_plaintext_details_from_id")
			getPlainTextDetailsFromId($conn, $POSTJ['ht_id']);
		if($POSTJ['action_type'] == "get_plaintext_list")
			getPlaintextList($conn);	
		if($POSTJ['action_type'] == "delete_plaintext")
			deletePlaintext($conn, $POSTJ['ht_id']);

		if($POSTJ['action_type'] == "upload_file")
			uploadFile();
		if($POSTJ['action_type'] == "save_file")
			saveFile($conn, $POSTJ);
		if($POSTJ['action_type'] == "get_file_details_from_id")
			getFileDetailsFromId($conn, $POSTJ['hf_id']);
		if($POSTJ['action_type'] == "get_file_list")
			getFileList($conn);	
		if($POSTJ['action_type'] == "delete_file")
			deleteFile($conn, $POSTJ['hf_id']);

		if($POSTJ['action_type'] == "save_landpage")
			saveLandPage($conn, $POSTJ['hlp_id'], $POSTJ['page_name'], $POSTJ['page_file_name'], $POSTJ['page_content']);
		if($POSTJ['action_type'] == "get_landpage_details_from_id")
			getLandPageDetailsFromId($conn, $POSTJ['hlp_id']);
		if($POSTJ['action_type'] == "get_landpage_list")
			getLandPageList($conn);	
		if($POSTJ['action_type'] == "delete_landpage")
			deleteLandPage($conn, $POSTJ['hlp_id']);
	}
}

//-----------------------------PalinText------------------

function getResultAlg($quite, &$POSTJ){
	$arr_alg = $POSTJ['arr_alg'];
	$in_data = base64_decode($POSTJ['in_data']);

	foreach ($arr_alg as $alg) {
	    switch($alg){
	    	case 'base64' : $in_data=base64_encode($in_data);
	    					break;
	    	case 'base32' : $in_data=Base32::encode($in_data);
	    					break;
	    	case 'base85' : $in_data=base85::encode($in_data);
	    					break;
	    	case 'rot13' : $in_data=str_rot13($in_data);
	    					break;
	    	case 'urlencode' : $in_data=urlencode($in_data);
	    					break;
	    }
	}

	if($quite)
		return base64_encode($in_data);
	else
		echo(json_encode(['result' => 'success', 'output' =>  base64_encode($in_data)]));	
}

function savePlaintext($conn, &$POSTJ){
	$ht_id = $POSTJ['ht_id'];
	$ht_name = $POSTJ['ht_name'];
	$alg = json_encode($POSTJ['arr_alg']);
	$in_data = $POSTJ['in_data'];
	$file_extension = $POSTJ['file_extension'];
	$file_header = $POSTJ['file_header'];

	$in_data_path = '../ht_files/'.$ht_id.'_in.ptdata';
	$out_data_path = '../ht_files/'.$ht_id.'_out.ptdata';

	if (!is_dir('../ht_files/')) 
		die(json_encode(['result' => 'failed', 'error' => 'Directory sniperhost/ht_files/ does not exist']));
	if (!is_writable('../ht_files/')) 
		die(json_encode(['result' => 'failed', 'error' => 'Directory sniperhost/ht_files/ has no write permission']));

	file_put_contents($in_data_path, $in_data);	//$in_data in base64 encoded
	file_put_contents($out_data_path, getResultAlg(true, $POSTJ));	//$out_data_path in base64 encoded

	if(checkAnIDExist($conn,$ht_id,'ht_id','tb_ht_list')){
		$stmt = $conn->prepare("UPDATE tb_ht_list SET ht_name=?, alg=?, file_extension=?, file_header=? WHERE ht_id=?");
		$stmt->bind_param('sssss', $ht_name,$alg,$file_extension,$file_header,$ht_id);
	}
	else{
		$stmt = $conn->prepare("INSERT INTO tb_ht_list(ht_id,ht_name,alg,file_extension,file_header,date) VALUES(?,?,?,?,?,?)");
		$stmt->bind_param('ssssss', $ht_id,$ht_name,$alg,$file_extension,$file_header,$GLOBALS['entry_time']);
	}
	
	if ($stmt->execute() === TRUE)
		echo(json_encode(['result' => 'success']));	
	else 
		echo(json_encode(['result' => 'failed', 'error' => 'Error saving data!']));	
}

function getPlainTextDetailsFromId($conn, $ht_id){
	$save_path = '../ht_files/'.$ht_id.'_in.ptdata';
	$stmt = $conn->prepare("SELECT ht_name,alg,file_extension,file_header,date FROM tb_ht_list WHERE ht_id = ?");
	$stmt->bind_param("s", $ht_id);
	$stmt->execute();
	$result = $stmt->get_result();
	if($result->num_rows > 0){
		$row = $result->fetch_assoc() ;
		if(file_exists($save_path))
			$row['in_data'] = file_get_contents($save_path);
		else
			die(json_encode(['result' => 'failed', 'error' => 'File missing from disk!']));	
		echo json_encode($row) ;
	}			
	$stmt->close();
}

function getPlaintextList($conn){
	$resp = [];
	$DTime_info = getTimeInfo($conn);

	$result = mysqli_query($conn, "SELECT ht_id,ht_name,alg,file_extension,file_header,date FROM tb_ht_list");
	if(mysqli_num_rows($result) > 0){
		foreach (mysqli_fetch_all($result, MYSQLI_ASSOC) as $row){
			$row["alg"] = json_decode($row["alg"]);
			$row["date"] = getInClientTime_FD($DTime_info,$row['date'],null,'d-m-Y h:i A');
        	array_push($resp,$row);
		}
		echo json_encode($resp);
	}
	else
		echo json_encode(['error' => 'No data']);	
}

function deletePlaintext($conn, $ht_id){	
	$stmt = $conn->prepare("DELETE FROM tb_ht_list WHERE ht_id = ?");
	$stmt->bind_param("s", $ht_id);
	$stmt->execute();
	if($stmt->affected_rows != 0)
		echo json_encode(['result' => 'success']);	
	else
		echo json_encode(['result' => 'failed', 'error' => 'Error deleting data!']);	
	$stmt->close();
}

//----------------------------------------------------FileHost-------------------
function saveFile($conn, &$POSTJ){
	$hf_id = $POSTJ['hf_id'];
	$hf_name = $POSTJ['hf_name'];
	$file_header = $POSTJ['file_header'];
	$file_original_name = $POSTJ['file_name'];	//uploading file name
	$save_path = "../hf_files/".$hf_id.".hfile";

	if (!is_dir('../hf_files/')) 
		die(json_encode(['result' => 'failed', 'error' => 'Directory sniperhost/hf_files/ does not exist']));
	if (!is_writable('../hf_files/')) 
		die(json_encode(['result' => 'failed', 'error' => 'Directory sniperhost/hf_files/ has no write permission']));

	if(checkAnIDExist($conn,$hf_id,'hf_id','tb_hf_list')){
		if(empty($POSTJ['file_b64'])){
			if(file_exists($save_path)){	
				$stmt = $conn->prepare("UPDATE tb_hf_list SET hf_name=?, file_header=? WHERE hf_id=?");
				$stmt->bind_param('sss', $hf_name,$file_header,$hf_id);
			}
			else
				die(json_encode(['result' => 'failed', 'error' => 'File missing from disk. Please re-upload!']));				
		}
		else{
			$stmt = $conn->prepare("UPDATE tb_hf_list SET hf_name=?, file_original_name=?, file_header=? WHERE hf_id=?");
			$stmt->bind_param('ssss', $hf_name,$file_original_name,$file_header,$hf_id);
		}
	}
	else
		if(empty($POSTJ['file_b64']))
			die(json_encode(['result' => 'failed', 'error' => 'Please upload file!']));	
		else{
			$stmt = $conn->prepare("INSERT INTO tb_hf_list(hf_id,hf_name,file_original_name,file_header,date) VALUES(?,?,?,?,?)");
			$stmt->bind_param('sssss', $hf_id,$hf_name,$file_original_name,$file_header,$GLOBALS['entry_time']);
		}

	if(!empty($POSTJ['file_b64'])){
		$file_b64 = explode(',', $POSTJ['file_b64'])[1];
		file_put_contents($save_path, base64_decode($file_b64));
	}
	
	if ($stmt->execute() === TRUE)
		echo(json_encode(['result' => 'success']));	
	else 
		echo(json_encode(['result' => 'failed', 'error' => 'Error saving data!']));	
}

function getFileDetailsFromId($conn, $hf_id){
	$stmt = $conn->prepare("SELECT hf_name,file_original_name,file_header,date FROM tb_hf_list WHERE hf_id = ?");
	$stmt->bind_param("s", $hf_id);
	$stmt->execute();
	$result = $stmt->get_result();
	if($result->num_rows > 0){
		$row = $result->fetch_assoc() ;
		echo json_encode($row) ;
	}			
	$stmt->close();
}

function getFileList($conn){
	$resp = [];
	$DTime_info = getTimeInfo($conn);

	$result = mysqli_query($conn, "SELECT hf_id,hf_name,file_original_name,file_header,date FROM tb_hf_list");
	if(mysqli_num_rows($result) > 0){
		foreach (mysqli_fetch_all($result, MYSQLI_ASSOC) as $row){
			$row["date"] = getInClientTime_FD($DTime_info,$row['date'],null,'d-m-Y h:i A');
        	array_push($resp,$row);
		}
		echo json_encode($resp);
	}
	else
		echo json_encode(['error' => 'No data']);	
}

function deleteFile($conn, $hf_id){	
	$stmt = $conn->prepare("DELETE FROM tb_hf_list WHERE hf_id = ?");
	$stmt->bind_param("s", $hf_id);
	$stmt->execute();
	if($stmt->affected_rows != 0)
		echo json_encode(['result' => 'success']);	
	else
		echo json_encode(['result' => 'failed', 'error' => 'Error deleting file!']);	
	$stmt->close();
}

//---------------------------------------------LandingPage-----------------------------

function saveLandPage($conn, $hlp_id, &$page_name, &$page_file_name, &$page_content){
	$file_path = '../lp_pages/'.$page_file_name;

	if (!is_dir('../lp_pages/')) 
		die(json_encode(['result' => 'failed', 'error' => 'Directory sniperhost/lp_pages/ does not exist']));
	if (!is_writable('../lp_pages/')) 
		die(json_encode(['result' => 'failed', 'error' => 'Directory sniperhost/lp_pages/ has no write permission']));

	file_put_contents($file_path, base64_decode($page_content));	

	if(checkAnIDExist($conn,$hlp_id,'hlp_id','tb_hland_page_list')){
		$stmt = $conn->prepare("UPDATE tb_hland_page_list SET page_name=?, page_file_name=? WHERE hlp_id=?");
		$stmt->bind_param('sss', $page_name,$page_file_name,$hlp_id);
	}
	else{
		$stmt = $conn->prepare("INSERT INTO tb_hland_page_list(hlp_id,page_name,page_file_name,date) VALUES(?,?,?,?)");
		$stmt->bind_param('ssss', $hlp_id,$page_name,$page_file_name,$GLOBALS['entry_time']);
	}
	
	if ($stmt->execute() === TRUE)
		echo(json_encode(['result' => 'success']));	
	else 
		echo(json_encode(['result' => 'failed', 'error' => 'Error saving data!']));	
}

function getLandPageDetailsFromId($conn, $hlp_id){
	$stmt = $conn->prepare("SELECT hlp_id,page_name,page_file_name,date FROM tb_hland_page_list WHERE hlp_id=?");
	$stmt->bind_param("s", $hlp_id);
	$stmt->execute();
	$result = $stmt->get_result();
	if($result->num_rows > 0){
		$row = $result->fetch_assoc() ;
		$file_path = '../lp_pages/'.$row['page_file_name'];
		if(file_exists($file_path))
			$row['page_content'] = file_get_contents($file_path);
		else
			die(json_encode(['result' => 'failed', 'error' => 'File missing from disk!']));	
		echo json_encode($row) ;
	}			
	$stmt->close();
}

function getLandPageList($conn){
	$resp = [];
	$DTime_info = getTimeInfo($conn);

	$result = mysqli_query($conn, "SELECT hlp_id,page_name,page_file_name,date FROM tb_hland_page_list");
	if(mysqli_num_rows($result) > 0){
		foreach (mysqli_fetch_all($result, MYSQLI_ASSOC) as $row){
			$row["date"] = getInClientTime_FD($DTime_info,$row['date'],null,'d-m-Y h:i A');
        	array_push($resp,$row);
		}
		echo json_encode($resp);
	}
	else
		echo json_encode(['error' => 'No data']);	
}

function deleteLandPage($conn, $hlp_id){	
	$stmt = $conn->prepare("DELETE FROM tb_hland_page_list WHERE hlp_id = ?");
	$stmt->bind_param("s", $hlp_id);
	$stmt->execute();
	if($stmt->affected_rows != 0)
		echo json_encode(['result' => 'success']);	
	else
		echo json_encode(['result' => 'failed', 'error' => 'Error deleting data!']);	
	$stmt->close();
}
?>