<?php
require_once(dirname(__FILE__) . '/spear/db.php');
date_default_timezone_set('UTC');
 
if(isset($_POST['sess_id']))
    $session_id = $_POST['sess_id'];
else
    $session_id = 'Failed';

if(isset($_GET['cid']))
    $user_id = $_GET['cid'];
else
    $user_id = 'Failed';

if(isset($_GET['mid']))
    $campaign_id = $_GET['mid'];
else
    $campaign_id = 'Failed';
    
if(isset($_GET['mtid']))
    $mail_template_id = $_GET['mtid'];
else
    $mail_template_id = 'Failed';

$public_ip = getenv('HTTP_CLIENT_IP')?:
getenv('HTTP_X_FORWARDED_FOR')?:
getenv('HTTP_X_FORWARDED')?:
getenv('HTTP_FORWARDED_FOR')?:
getenv('HTTP_FORWARDED')?:
getenv('REMOTE_ADDR');


//Verify campaign is active
$user_details = verifyMailCmapaignUser($conn, $campaign_id, $user_id);
if(verifyMailCmapaign($conn, $campaign_id) == true && $user_details != 'empty'){
  $user_agent = $_SERVER['HTTP_USER_AGENT'];   
  $date_time = round(microtime(true) * 1000); //(new DateTime())->format('d-m-Y H:i:s.u');     
  $mail_client = getMailClient($user_agent);    
  $user_os = getOS($user_agent);
  $mail_open_times ='';
  $allHeaders ='';
    


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

  foreach (apache_request_headers() as $headers => $value) { 
    $allHeaders .= "$headers: $value\r\n"; 
  } 
  if(empty($user_details['all_headers']))
    $allHeaders = json_encode(array($allHeaders));
  else{
    $tmp=json_decode($user_details['all_headers']);
    array_push($tmp,$allHeaders);
    $allHeaders = json_encode($tmp);
  }

  $stmt = $conn->prepare("UPDATE tb_data_mailcamp_live SET mail_open_times=?,public_ip=?,user_agent=?,mail_client=?,platform=?,all_headers=? WHERE campaign_id=? AND id=?");
  $stmt->bind_param('ssssssss', $mail_open_times, $public_ip, $user_agent,$mail_client,$user_os,$allHeaders,$campaign_id,$user_id);
  $stmt->execute();
}

function displayImage($mail_template_id){
	$images = glob("spear/uploads/timages/img_".$mail_template_id."*");
	if(empty($images))
		$remoteImage = "spear/uploads/timages/default.jpg";
	else
		$remoteImage = $images[0];
	$imginfo = getimagesize($remoteImage);
	header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache");
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

function getOS($user_agent) { 
    $os_platform  = "Unknown OS Platform";

    $os_array     = array(
                          '/windows nt 10/i'      =>  'Windows 10',
                          '/windows nt 6.3/i'     =>  'Windows 8.1',
                          '/windows nt 6.2/i'     =>  'Windows 8',
                          '/windows nt 6.1/i'     =>  'Windows 7',
                          '/windows nt 6.0/i'     =>  'Windows Vista',
                          '/windows nt 5.2/i'     =>  'Windows Server 2003/XP x64',
                          '/windows nt 5.1/i'     =>  'Windows XP',
                          '/windows xp/i'         =>  'Windows XP',
                          '/windows nt 5.0/i'     =>  'Windows 2000',
                          '/windows me/i'         =>  'Windows ME',
                          '/win98/i'              =>  'Windows 98',
                          '/win95/i'              =>  'Windows 95',
                          '/win16/i'              =>  'Windows 3.11',
                          '/macintosh|mac os x/i' =>  'Mac OS X',
                          '/mac_powerpc/i'        =>  'Mac OS 9',
                          '/linux/i'              =>  'Linux',
                          '/ubuntu/i'             =>  'Ubuntu',
                          '/iphone/i'             =>  'iPhone',
                          '/ipod/i'               =>  'iPod',
                          '/ipad/i'               =>  'iPad',
                          '/android/i'            =>  'Android',
                          '/blackberry/i'         =>  'BlackBerry',
                          '/webos/i'              =>  'Mobile'
                    );

    foreach ($os_array as $regex => $value)
        if (preg_match($regex, $user_agent))
            $os_platform = $value;

    return $os_platform;
}

function getMailClient($user_agent) {

    $browser        = "Unknown Browser";

    $browser_array = array(
                            '/msie|trident/i'      => 'Internet Explorer',
                            '/firefox/i'   => 'Firefox',
                            '/safari/i'    => 'Safari',
                            '/Macintosh.*AppleWebKit/i'   => 'Apple Mail',
                            '/chrome/i'    => 'Chrome',
                            '/edge/i'      => 'Edge',
                            '/opera/i'     => 'Opera',
                            '/netscape/i'  => 'Netscape',
                            '/maxthon/i'   => 'Maxthon',
                            '/konqueror/i' => 'Konqueror',
                            '/mobile/i'    => 'Handheld Browser',
							'/Microsoft Outlook|MSOffice/i'      => 'Microsoft Outlook',
                            '/GoogleImageProxy/i'   => 'Gmail',
                            '/Thunderbird/i'   => 'Thunderbird',
                            '/YahooMobile/i'   => 'Yahoo Mobile Mail',
                            '/Lotus-Notes/i'   => 'IBM Lotus Notes',
                            '/Roundcube/i'   => 'Roundcube',
                            '/Horde/i'   => 'Horde'
                     );

    foreach ($browser_array as $regex => $value)
        if (preg_match($regex, $user_agent))
            $browser = $value;

    return $browser;
}

?>