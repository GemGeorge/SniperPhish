<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type');
require_once(dirname(__FILE__) . '/spear/db.php');
require_once(dirname(__FILE__) . '/spear/common_functions.php');
require_once(dirname(__FILE__) . '/spear/libs/browser_detect/BrowserDetection.php');
date_default_timezone_set('UTC');
//-------------------------------------
if (isset($_POST))
    $POSTJ = json_decode(file_get_contents('php://input'),true);
else
    die();

if(isset($POSTJ['cid']) && !empty($POSTJ['cid']))
    $cid = doFilter($POSTJ['cid'],'ALPHA_NUM');
else
    die("No cid");
    
if(isset($POSTJ['sess_id']))
    $session_id = doFilter($POSTJ['sess_id'],'ALPHA_NUM');
else
    $session_id = 'Failed';

if(isset($POSTJ['trackerId']))
    $trackerId = doFilter($POSTJ['trackerId'],'ALPHA_NUM');
else
    $trackerId = 'Failed';

$ua_info = new Wolfcast\BrowserDetection();
$public_ip = getenv('HTTP_CLIENT_IP')?:
getenv('HTTP_X_FORWARDED_FOR')?:
getenv('HTTP_X_FORWARDED')?:
getenv('HTTP_FORWARDED_FOR')?:
getenv('HTTP_FORWARDED')?:
getenv('REMOTE_ADDR');
$public_ip = htmlspecialchars($public_ip);

$user_agent = htmlspecialchars($_SERVER['HTTP_USER_AGENT']);    

$date_time = round(microtime(true) * 1000);
$user_browser = $ua_info->getName().' '.($ua_info->getVersion() == "unknown"?"":$ua_info->getVersion());
$user_os = $ua_info->getPlatformVersion();
$device_type = $ua_info->isMobile()?"Mobile":"Desktop";
try{
    if(empty($POSTJ['ip_info']))
        $ip_info = getIPInfo($conn, $public_ip);
    else
        $ip_info = craftIPInfoArr(json_decode($POSTJ['ip_info'],true));
}
catch (Exception $e) {
    $ip_info = getIPInfo($conn, $public_ip);
}

//-----------------------------------
if(isset($POSTJ['screen_res']))
    $screen_res = htmlspecialchars($POSTJ['screen_res']);
else
    $screen_res = 'Failed'; 

//Check tracker stopped/paused
$stmt = $conn->prepare("SELECT active FROM tb_core_web_tracker_list WHERE tracker_id = ?");
$stmt->bind_param("s", $trackerId);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc() ;
if($result["active"] == 0)
  return;
  
$page = $POSTJ['page'];
if($page == 0){  //page visit
	$stmt = $conn->prepare("INSERT INTO tb_data_webpage_visit(tracker_id,session_id,cid,public_ip,ip_info,user_agent,screen_res,time,browser,platform,device_type) VALUES(?,?,?,?,?,?,?,?,?,?,?)");
	$stmt->bind_param('sssssssssss', $trackerId,$session_id,$cid,$public_ip,$ip_info,$user_agent,$screen_res,$date_time,$user_browser,$user_os,$device_type);
	if ($stmt->execute() === TRUE)
		die('success'); 
	else 
		die("failed"); 
}  
elseif(is_numeric($page)){
    foreach ($POSTJ['form_field_data'] as $i => $field_data) {
        $POSTJ['form_field_data'][$i] = htmlspecialchars($POSTJ['form_field_data'][$i]);
    }
    $form_field_data = json_encode($POSTJ['form_field_data']);
	
	$stmt = $conn->prepare("INSERT INTO tb_data_webform_submit(tracker_id,session_id,cid,public_ip,ip_info,user_agent,screen_res,time,browser,platform,device_type,page,form_field_data) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?)");
	$stmt->bind_param('sssssssssssss', $trackerId,$session_id,$cid,$public_ip,$ip_info,$user_agent,$screen_res,$date_time,$user_browser,$user_os,$device_type,$page,$form_field_data);
	if ($stmt->execute() === TRUE)
		die('success'); 
	else 
		die("failed"); 
}

//-----------------------------------------


?>