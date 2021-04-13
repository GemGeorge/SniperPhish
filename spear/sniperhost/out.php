<?php
require_once(dirname(__FILE__,2) . '/db.php');

if(isset($_GET['ht']))	//host text
	displayHostPlainText($conn,$_GET['ht']);
else
	if(isset($_GET['hf']))	//host file
		displayHostFile($conn,$_GET['hf']);
	else
		die("error");

//-------------------------------------------

function displayHostPlainText($conn,$ht_id){
	$ht_id = explode('.', $ht_id)[0];
	$stmt = $conn->prepare("SELECT file_header FROM tb_ht_list WHERE ht_id = ?");
	$stmt->bind_param("s", $ht_id);
	$stmt->execute();
	if($row =  $stmt->get_result()->fetch_assoc()){
		if($row['file_header'] != 'None')	//else set default header
			header('Content-Type: '.$row['file_header']);
		echo base64_decode(file_get_contents('ht_files/'.$ht_id.'_out.ptdata'));
	}
}

function displayHostFile($conn,$hf_id){
	$hf_id = explode('.', $hf_id)[0];
	$stmt = $conn->prepare("SELECT file_header FROM tb_hf_list WHERE hf_id = ?");
	$stmt->bind_param("s", $hf_id);
	$stmt->execute();
	if($row =  $stmt->get_result()->fetch_assoc()){
		if($row['file_header'] != 'None')	//else set default header
			header('Content-Type: '.$row['file_header']);
		echo file_get_contents("hf_files/".$hf_id.".hfile");
	}
}

?>