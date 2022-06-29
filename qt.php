<?php
require_once(dirname(__FILE__) . '/spear/config/db.php');
require_once(dirname(__FILE__) . '/spear/manager/common_functions.php');
require_once(dirname(__FILE__) . '/spear/libs/browser_detect/BrowserDetection.php');
date_default_timezone_set('UTC');

if(isset($_REQUEST['rid']))
    $user_id = doFilter($_REQUEST['rid'],'ALPHA_NUM');
else
    $user_id = 'Failed';

if(isset($_REQUEST['tid']))
    $tracker_id = doFilter($_REQUEST['tid'],'ALPHA_NUM');
else
    $tracker_id = 'Failed';

$ua_info = new Wolfcast\BrowserDetection();
$public_ip = getPublicIP();

//Verify campaign is active
if(verifyQuickTracker($conn, $tracker_id) == true){
    $user_agent = $_SERVER['HTTP_USER_AGENT'];   
    $date_time = round(microtime(true) * 1000); //(new DateTime())->format('d-m-Y H:i:s.u');     
    $user_os = $ua_info->getPlatformVersion();
    if(empty($POSTJ['ip_info']))
        $ip_info = getIPInfo($conn, $public_ip);
    else
        $ip_info = json_encode(craftIPInfoArr($POSTJ['ip_info']));
    $allHeaders ='';

    $mail_client = getMailClient($user_agent);    
    if($mail_client == "unknown")
        $mail_client = $ua_info->getName().' '.($ua_info->getVersion() == "unknown"?"":$ua_info->getVersion());

    foreach (apache_request_headers() as $headers => $value) { 
        $allHeaders .= htmlspecialchars("$headers: $value\r\n"); 
    }

    $stmt = $conn->prepare("INSERT INTO tb_data_quick_tracker_live(tracker_id,rid,public_ip,ip_info,user_agent,mail_client,platform,all_headers,time) VALUES(?,?,?,?,?,?,?,?,?)");
    $stmt->bind_param('sssssssss', $tracker_id,$user_id,$public_ip,$ip_info,$user_agent,$mail_client,$user_os,$allHeaders,$date_time);
    $stmt->execute();
}

function displayImage(){
    $remoteImage = "spear/uploads/timages/default.jpg";
    $imginfo = getimagesize($remoteImage);
    header("Cache-Control: no-store");
    header("Content-type: {$imginfo['mime']}");
    readfile($remoteImage);
}
displayImage();
//-----------------------------------------
function verifyQuickTracker($conn, $tracker_id){
    $stmt = $conn->prepare("SELECT active FROM tb_core_quick_tracker_list WHERE tracker_id = ?");
    $stmt->bind_param("s", $tracker_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    if($row['active'] == 1)//1=>active
        return true;
    else
      return false;
}
?>