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

if(isset($_GET['tid']))
    $tracker_id = $_GET['tid'];
else
    $tracker_id = 'Failed';

$public_ip = getenv('HTTP_CLIENT_IP')?:
getenv('HTTP_X_FORWARDED_FOR')?:
getenv('HTTP_X_FORWARDED')?:
getenv('HTTP_FORWARDED_FOR')?:
getenv('HTTP_FORWARDED')?:
getenv('REMOTE_ADDR');


//Verify campaign is active
if(verifySimpleTracker($conn, $tracker_id) == true){
  $user_agent = $_SERVER['HTTP_USER_AGENT'];   
  $date_time = round(microtime(true) * 1000); //(new DateTime())->format('d-m-Y H:i:s.u');     
  $mail_client = getMailClient($user_agent);    
  $user_os = getOS($user_agent);
  $user_agent = $_SERVER['HTTP_USER_AGENT'];
  $allHeaders ='';

  foreach (apache_request_headers() as $headers => $value) { 
    $allHeaders .= "$headers: $value\r\n"; 
  }

  $stmt = $conn->prepare("INSERT INTO tb_data_simple_tracker_live(tracker_id,cid,public_ip,user_agent,mail_client,platform,all_headers,time) VALUES(?,?,?,?,?,?,?,?)");
  $stmt->bind_param('ssssssss', $tracker_id,$user_id,$public_ip,$user_agent,$mail_client,$user_os,$allHeaders,$date_time);
  $stmt->execute();
}

function displayImage(){
	$remoteImage = "spear/uploads/timages/default.jpg";
	$imginfo = getimagesize($remoteImage);
	header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache");
	header("Content-type: {$imginfo['mime']}");
	readfile($remoteImage);
}
displayImage();
//-----------------------------------------
function verifySimpleTracker($conn, $tracker_id){
  $stmt = $conn->prepare("SELECT active FROM tb_core_simple_tracker_list WHERE tracker_id = ?");
  $stmt->bind_param("s", $tracker_id);
  $stmt->execute();
  $row = $stmt->get_result()->fetch_assoc();
  if($row['active'] == 1)//1=>active
      return true;
  else
    return false;
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