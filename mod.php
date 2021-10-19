<?php
require_once(dirname(__FILE__) . '/spear/libs/qr_barcode/qrcode.php');
require_once(dirname(__FILE__) . '/spear/libs/qr_barcode/barcode.php');
require_once(dirname(__FILE__) . '/spear/db.php');
require_once(dirname(__FILE__) . '/spear/common_functions.php');

if(isset($_GET['content']))
	$content = $_GET['content'];
else
	$content = ' ';

if(isset($_GET['type']))
	switch ($_GET['type']) {
		case 'qr_ir': 
		case 'qr_b64': 
		case 'qr_att': displayQRImage(); break;		
		case 'bar_ir': 
		case 'bar_b64':
		case 'bar_att': displayBarcodeImage(); break;
	}

if(isset($_GET['tlink']))
	getTrackerCode($conn, $_GET['tlink']);
if(isset($_GET['mbf']) && is_numeric($_GET['mbf']))
	getMailBodyFile($_GET['mbf']);

//--------------------------------------------------

function displayQRImage(){
	$generator = new barcode_generator();
	if(isset($_GET['options']))
		$options = $_GET['options'];
	else
		$options = ['sx'=>5, 'sf'=>5];

	header('Content-Type: image/png');
	$generator->output_image("png", "qr", $GLOBALS['content'], $options);
}

function displayBarcodeImage(){
	header('Content-Type: image/png');
	echo barcode( "", $GLOBALS['content'], 50, "horizontal", "code128", false, 1);
}

function getTrackerCode($conn, $tracker_id){
	$stmt = $conn->prepare("SELECT content_js FROM tb_core_web_tracker_list WHERE tracker_id = ?");
	$stmt->bind_param("s", $tracker_id);
	$stmt->execute();
	$result = $stmt->get_result();	
	header('Content-Type: application/javascript');
	if($result->num_rows != 0){
		$row = $result->fetch_row() ;
		echo ($row[0]) ;
	}			
	$stmt->close();
}

function getMailBodyFile($mbf){
	$mbf = doFilter($mbf,'NUM');
	$files = glob('spear/uploads/attachments/*'.$mbf.".mbf");

	if(!empty($files)){
		$file = $files[0];
		$mime = mime_content_type($file);
		header("Content-type: ".$mime);

		if(strstr($mime, "video/"))
			readfile($file);
		else if(strstr($mime, "image/"))
			readfile($file);
	}
}
?>