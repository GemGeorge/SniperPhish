<?php
require_once(dirname(__FILE__) . '/spear/db.php');
require_once(dirname(__FILE__) . '/spear/common_functions.php');
require_once(dirname(__FILE__) . '/spear/libs/browser_detect/BrowserDetection.php');
date_default_timezone_set('UTC');

if(isset($_GET['cid']))
    $user_id = doFilter($_GET['cid'],'ALPHA_NUM');
else
    $user_id = 'Failed';

if(isset($_GET['mid']))
    $campaign_id = doFilter($_GET['mid'],'ALPHA_NUM');
else
    $campaign_id = 'Failed';
    
if(isset($_GET['mtid'])){
    $mail_template_id = explode('_', $_GET['mtid'])[0];   //expects mtid_<random number>
    $mail_template_id = doFilter($mail_template_id,'ALPHA_NUM');
}
else
    $mail_template_id = 'Failed';

$ua_info = new Wolfcast\BrowserDetection();
$public_ip = getenv('HTTP_CLIENT_IP')?:
getenv('HTTP_X_FORWARDED_FOR')?:
getenv('HTTP_X_FORWARDED')?:
getenv('HTTP_FORWARDED_FOR')?:
getenv('HTTP_FORWARDED')?:
getenv('REMOTE_ADDR');
$public_ip = htmlspecialchars($public_ip);

//Verify campaign is active
$user_details = verifyMailCmapaignUser($conn, $campaign_id, $user_id);
if(verifyMailCmapaign($conn, $campaign_id) == true && $user_details != 'empty'){

    $user_agent = htmlspecialchars($_SERVER['HTTP_USER_AGENT']);   
    $date_time = round(microtime(true) * 1000); //(new DateTime())->format('d-m-Y H:i:s.u');    
    $user_os = $ua_info->getPlatformVersion();
    $device_type = $ua_info->isMobile()?"Mobile":"Desktop";
	$ip_info = getIPInfo($conn, $public_ip);
    $mail_open_times ='';
    $allHeaders ='';

    $mail_client = getMailClient($user_agent);    
    if($mail_client == "unknown")
        $mail_client = $ua_info->getName().' '.($ua_info->getVersion() == "unknown"?"":$ua_info->getVersion());
      
    if(empty($user_details['mail_open_times']))
        $mail_open_times = json_encode(array($date_time));
    else{
        $tmp=json_decode($user_details['mail_open_times']);
        array_push($tmp,$date_time);
        $mail_open_times = json_encode($tmp);
    }

    if(empty($user_details['public_ip']))
        $public_ip = json_encode(array($public_ip));
    else{
        $tmp=json_decode($user_details['public_ip']);
        array_push($tmp,$public_ip);
        $public_ip = json_encode($tmp);
    }

    if(!empty($user_details['ip_info']))
        $ip_info = $user_details['ip_info'];

    if(empty($user_details['user_agent']))
        $user_agent = json_encode(array($user_agent));
    else{
        $tmp=json_decode($user_details['user_agent']);
        array_push($tmp,$user_agent);
        $user_agent = json_encode($tmp);
    }

    if(empty($user_details['mail_client']))
        $mail_client = json_encode(array($mail_client));
    else{
        $tmp=json_decode($user_details['mail_client']);
        array_push($tmp,$mail_client);
        $mail_client = json_encode($tmp);
    }

    if(empty($user_details['platform']))
        $user_os = json_encode(array($user_os));
    else{
        $tmp=json_decode($user_details['platform']);
        array_push($tmp,$user_os);
        $user_os = json_encode($tmp);
    }

    if(empty($user_details['device_type']))
        $device_type = json_encode(array($device_type));
    else{
        $tmp=json_decode($user_details['device_type']);
        array_push($tmp,$device_type);
        $device_type = json_encode($tmp);
    }

    foreach (apache_request_headers() as $headers => $value) { 
        $allHeaders .= htmlspecialchars("$headers: $value\r\n"); 
    } 
    if(empty($user_details['all_headers']))
        $allHeaders = json_encode(array($allHeaders));
    else{
        $tmp=json_decode($user_details['all_headers']);
        array_push($tmp,$allHeaders);
        $allHeaders = json_encode($tmp);
    }

    $stmt = $conn->prepare("UPDATE tb_data_mailcamp_live SET mail_open_times=?,public_ip=?,ip_info=?,user_agent=?,mail_client=?,platform=?,device_type=?,all_headers=? WHERE campaign_id=? AND id=?");
    $stmt->bind_param('ssssssssss', $mail_open_times,$public_ip,$ip_info,$user_agent,$mail_client,$user_os,$device_type,$allHeaders,$campaign_id,$user_id);
    $stmt->execute();
}

function displayImage($mail_template_id){
  	$images = glob("spear/uploads/timages/".$mail_template_id.".timg");
  	if(empty($images))
  		  $remoteImage = "spear/uploads/timages/default.jpg";
  	else
  		  $remoteImage = $images[0];
  	$imginfo = getimagesize($remoteImage);
  	header("Cache-Control: no-store");
  	header("Content-type: {$imginfo['mime']}");
  	readfile($remoteImage);
}
displayImage($mail_template_id);

//-----------------------------------------
function verifyMailCmapaign($conn, $campaign_id){
    $stmt = $conn->prepare("SELECT scheduled_time,camp_status FROM tb_core_mailcamp_list where campaign_id = ?");
    $stmt->bind_param("s", $campaign_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if($row = $result->fetch_assoc()){
        if($row['camp_status'] == 2 || $row['camp_status'] == 4)//If in-progress
          return true;
    } 
    return false;
}

function verifyMailCmapaignUser($conn, $campaign_id, $id){
    $stmt = $conn->prepare("SELECT * FROM tb_data_mailcamp_live WHERE campaign_id = ? AND id=?");
    $stmt->bind_param("ss", $campaign_id,$id);
    $stmt->execute();
    $result = $stmt->get_result();
    if($result->num_rows > 0){
        $row = $result->fetch_assoc() ;
        return $row;
    }
    else    
        return 'empty';
}
?>