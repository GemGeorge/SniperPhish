<?php
//-------------------Session check-----------------------
require_once(dirname(__FILE__,2) . '/db.php');
require_once(dirname(__FILE__,2) . '/session_manager.php');
require_once(dirname(__FILE__,2) . '/common_functions.php');
require_once("lib/Base32.php");
require_once("lib/base85.class.php");
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
	}
}

//-----------------------------

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

	$in_data_path = 'ht_files/'.$ht_id.'_in.ptdata';
	$out_data_path = 'ht_files/'.$ht_id.'_out.ptdata';

	if (!is_dir('ht_files/')) 
		die(json_encode(['result' => 'failed', 'error' => 'Directory sniperhost/ht_files/ does not exist']));
	if (!is_writable('ht_files/')) 
		die(json_encode(['result' => 'failed', 'error' => 'Directory sniperhost/ht_files/ has no write permission']));

	file_put_contents($in_data_path, $in_data);	//$in_data in base64 encoded
	file_put_contents($out_data_path, getResultAlg(true, $POSTJ));	//$out_data_path in base64 encoded

	if(checkPlaintextIDExist($conn,$ht_id)){
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

function checkPlaintextIDExist($conn,$ht_id){
	$stmt = $conn->prepare("SELECT COUNT(*) FROM tb_ht_list WHERE ht_id = ?");
	$stmt->bind_param("s", $ht_id);
	$stmt->execute();
	$row = $stmt->get_result()->fetch_row();
	if($row[0] > 0)
		return true;
	else
		return false;
}

function getPlainTextDetailsFromId($conn, $ht_id){
	$save_path = 'ht_files/'.$ht_id.'_in.ptdata';
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
	$result = mysqli_query($conn, "SELECT ht_id,ht_name,alg,file_extension,file_header,date FROM tb_ht_list");
	if(mysqli_num_rows($result) > 0)
		echo json_encode(mysqli_fetch_all($result, MYSQLI_ASSOC));
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

//----------------------------------------------------
function saveFile($conn, &$POSTJ){
	$hf_id = $POSTJ['hf_id'];
	$hf_name = $POSTJ['hf_name'];
	$file_header = $POSTJ['file_header'];
	$file_original_name = $POSTJ['file_name'];	//uploading file name
	$save_path = "hf_files/".$hf_id.".hfile";

	if (!is_dir('hf_files/')) 
		die(json_encode(['result' => 'failed', 'error' => 'Directory sniperhost/hf_files/ does not exist']));
	if (!is_writable('hf_files/')) 
		die(json_encode(['result' => 'failed', 'error' => 'Directory sniperhost/hf_files/ has no write permission']));

	if(checkFileIDExist($conn,$hf_id)){
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

function checkFileIDExist($conn,$hf_id){
	$stmt = $conn->prepare("SELECT COUNT(*) FROM tb_hf_list WHERE hf_id = ?");
	$stmt->bind_param("s", $hf_id);
	$stmt->execute();
	$row = $stmt->get_result()->fetch_row();
	if($row[0] > 0)
		return true;
	else
		return false;
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
	$result = mysqli_query($conn, "SELECT hf_id,hf_name,file_original_name,file_header,date FROM tb_hf_list");
	if(mysqli_num_rows($result) > 0)
		echo json_encode(mysqli_fetch_all($result, MYSQLI_ASSOC));
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
?>