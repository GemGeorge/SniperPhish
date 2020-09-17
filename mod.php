<?php
require_once(dirname(__FILE__) . '/spear/libs/qr_barcode/barcode.php');
require_once(dirname(__FILE__) . '/spear/db.php');

if(isset($_GET['content']))
	$content = $_GET['content'];
else
	$content = ' ';

if(isset($_GET['type']))
switch ($_GET['type']) {
	case 'qr': displayQRImage(); break;
	
	case 'bar': displayBarcodeImage(); break;
}

if(isset($_GET['tlink']))
	getTrackerCode($conn, $_GET['tlink']);



function displayQRImage(){
	$generator = new barcode_generator();

	if(isset($_GET['format']))
		$format = $_GET['format'];
	else
		$format = 'png';

	if(isset($_GET['symbology']))
		$symbology = $_GET['symbology'];
	else
		$symbology = 'qr';

	if(isset($_GET['options']))
		$options = $_GET['options'];
	else
		$options = ['sx'=>5, 'sf'=>5];
	$generator->output_image($format, $symbology, $GLOBALS['content'], $options);
}

function displayBarcodeImage(){
	$generator = new barcode_generator();

	if(isset($_GET['format']))
		$format = $_GET['format'];
	else
		$format = 'png';

	if(isset($_GET['symbology']))
		$symbology = $_GET['symbology'];
	else
		$symbology = 'upc-a';

	if(isset($_GET['options']))
		$options = $_GET['options'];
	else
		$options = ['sx'=>2, 'sf'=>2];
	$generator->output_image($format, $symbology, $GLOBALS['content'], $options);
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
?>