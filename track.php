<?php
header('Access-Control-Allow-Origin: *');
require_once(dirname(__FILE__) . '/spear/db.php');
date_default_timezone_set('UTC');

if(isset($_POST['cid']) && !empty($_POST['cid']))
    $cid = $_POST['cid'];
else
    die("No cid");
    
if(isset($_POST['sess_id']))
    $session_id = $_POST['sess_id'];
else
    $session_id = 'Failed';

$public_ip = getenv('HTTP_CLIENT_IP')?:
getenv('HTTP_X_FORWARDED_FOR')?:
getenv('HTTP_X_FORWARDED')?:
getenv('HTTP_FORWARDED_FOR')?:
getenv('HTTP_FORWARDED')?:
getenv('REMOTE_ADDR');

if(isset($_POST['private_ip']))
    $internal_ip = $_POST['private_ip'];
else
    $internal_ip = 'Failed';

$user_agent = $_SERVER['HTTP_USER_AGENT'];    

$date_time = round(microtime(true) * 1000); //(new DateTime())->format('d-m-Y H:i:s.u');    
$user_browser = getBrowser();    
$user_os = getOS();

if(isset($_POST['trackerId']))
    $trackerId = $_POST['trackerId'];
else
    $trackerId = 'Failed';

if(isset($_POST['login_username']))
    $login_username = $_POST['login_username'];
else
    $login_username = 'Failed';

if(isset($_POST['login_pwd']))
    $login_pwd = $_POST['login_pwd'];
else
    $login_pwd = 'Failed';    

//Check tracker stopped/paused
$stmt = $conn->prepare("SELECT active FROM tb_core_web_tracker_list WHERE tracker_id = ?");
$stmt->bind_param("s", $trackerId);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc() ;
if($result["active"] == 0)
  return;
  

if($_POST['page'] == 0){  //page visit
	$stmt = $conn->prepare("INSERT INTO tb_data_webpage_visit(tracker_id,session_id,cid,public_ip,internal_ip,user_agent,time,browser,platform) VALUES(?,?,?,?,?,?,?,?,?)");
	$stmt->bind_param('sssssssss', $trackerId, $session_id, $cid, $public_ip, $internal_ip, $user_agent,$date_time,$user_browser,$user_os);
	if ($stmt->execute() === TRUE)
		die('success'); 
	else 
		die("failed"); 
}
  
elseif(is_numeric($_POST['page'])){
  $page = $_POST['page'];
  $form_field_data = $_POST['form_field_data'];
	
	$stmt = $conn->prepare("INSERT INTO tb_data_webform_submit(tracker_id,session_id,cid,public_ip,internal_ip,user_agent,time,browser,platform,page,form_field_data) VALUES(?,?,?,?,?,?,?,?,?,?,?)");
	$stmt->bind_param('sssssssssss', $trackerId, $session_id, $cid, $public_ip, $internal_ip, $user_agent,$date_time,$user_browser,$user_os,$page,$form_field_data);
	if ($stmt->execute() === TRUE)
		die('success'); 
	else 
		die("failed"); 
}

//-----------------------------------------
function getOS() { 

    global $user_agent;

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

function getBrowser() {

    global $user_agent;

    $browser        = "Unknown Browser";

    $browser_array = array(
                            '/msie|trident/i'      => 'Internet Explorer',
                            '/firefox/i'   => 'Firefox',
                            '/safari/i'    => 'Safari',
                            '/chrome/i'    => 'Chrome',
                            '/edge/i'      => 'Edge',
                            '/opera/i'     => 'Opera',
                            '/netscape/i'  => 'Netscape',
                            '/maxthon/i'   => 'Maxthon',
                            '/konqueror/i' => 'Konqueror',
                            '/mobile/i'    => 'Handheld Browser'
                     );

    foreach ($browser_array as $regex => $value)
        if (preg_match($regex, $user_agent))
            $browser = $value;

    return $browser;
}

?>