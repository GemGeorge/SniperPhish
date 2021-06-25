<?php
require_once(dirname(__FILE__) . '/session_manager.php');
if(isSessionValid() == false)
	die("Access denied");
//-------------------------------------------------------
date_default_timezone_set('UTC');
$entry_time = (new DateTime())->format('d-m-Y h:i A');
header('Content-Type: application/json');

if (isset($_POST)) {
	$POSTJ = json_decode(file_get_contents('php://input'),true);

	if(isset($POSTJ['action_type'])){
		if($POSTJ['action_type'] == "save_mcamp_config")
				saveMCampConfig($conn,$POSTJ);
		if($POSTJ['action_type'] == "delete_mcamp_config")
				deleteMCampConfig($conn,$POSTJ['mconfig_id']);
		if($POSTJ['action_type'] == "get_mcamp_config_details")
				getMcampConfigDetails($conn);
		if($POSTJ['action_type'] == "get_mcamp_config_details_from_id")
				getMcampConfigDetailsFromId($conn,$POSTJ['mconfig_id']);
	}
}
else
	die();
//----------------------------------------------------------------------
function saveMCampConfig($conn,&$POSTJ){
	$mconfig_id = $POSTJ['mconfig_id'];
	$mconfig_name = $POSTJ['mconfig_name'];
    $mconfig_data = json_encode($POSTJ['mconfig_data']);

    if($mconfig_id == "default" || strcasecmp($mconfig_name,"Default Configuration") == 0)
		echo(json_encode(['result' => 'failed', 'error' => 'Can not update default configuration']));	
	else{
		if(checkMConfigIdExist($conn,$mconfig_id)){
			$stmt = $conn->prepare("UPDATE tb_core_mailcamp_config SET mconfig_name=?, mconfig_data=? WHERE mconfig_id=?");
			$stmt->bind_param('sss', $mconfig_name,$mconfig_data,$mconfig_id);
		}
		else{
			$stmt = $conn->prepare("INSERT INTO tb_core_mailcamp_config(mconfig_id, mconfig_name, mconfig_data, date) VALUES(?,?,?,?)");
			$stmt->bind_param('ssss', $mconfig_id,$mconfig_name,$mconfig_data,$GLOBALS['entry_time']);
		}
		
		if ($stmt->execute() === TRUE){
			echo json_encode(['result' => 'success']);	
		}
		else 
			echo json_encode(['result' => 'failed', 'error' => 'Error saving data! '.$stmt->error]);	
	}
}

function deleteMCampConfig($conn,$mconfig_id){	
	$stmt = $conn->prepare("DELETE FROM tb_core_mailcamp_config WHERE mconfig_id=?");
	$stmt->bind_param("s", $mconfig_id);
	$stmt->execute();

	if($stmt->affected_rows != 0)
		echo json_encode(['result' => 'success']);	
	else
		echo json_encode(['result' => 'failed', 'error' => $stmt->error]);	
	$stmt->close();
}

function getMcampConfigDetails($conn){
	$result = mysqli_query($conn, "SELECT mconfig_id,mconfig_name FROM tb_core_mailcamp_config");
	if(mysqli_num_rows($result) > 0)
		echo json_encode(mysqli_fetch_all($result, MYSQLI_ASSOC),JSON_FORCE_OBJECT);
	else
		echo json_encode(['error' => 'No data']);	
}

function getMcampConfigDetailsFromId($conn,$mconfig_id){
	$stmt = $conn->prepare("SELECT * FROM tb_core_mailcamp_config where mconfig_id = ?");
	$stmt->bind_param("s", $mconfig_id);
	$stmt->execute();
	$result = $stmt->get_result();
	if($result->num_rows != 0){
		$row = $result->fetch_assoc();
		$row["mconfig_data"] = json_decode($row["mconfig_data"]);	//avoid double json encoding
		echo (json_encode($row));
	}
	else
		echo json_encode(['error' => 'No data']);		
	$stmt->close();
}

function checkMConfigIdExist($conn,$mconfig_id){
	$stmt = $conn->prepare("SELECT COUNT(*) FROM tb_core_mailcamp_config WHERE mconfig_id=?");
	$stmt->bind_param("s", $mconfig_id);
	$stmt->execute();
	$row = $stmt->get_result()->fetch_row();
	if($row[0] > 0)
		return true;
	else
		return false;
}
?>