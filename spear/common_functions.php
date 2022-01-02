<?php
function checkInstallation(){
    $db_file = dirname(__FILE__) . '/db.php';
    
    if (file_exists($db_file)) {
        require_once(dirname(__FILE__) . '/db.php');
        
        $result = mysqli_query($conn, "SHOW TABLES FROM $curr_db");
        if (mysqli_num_rows($result) > 0)
            die("Already installed! Click <a href='/spear'>here</a> to login");
        else
            return false;
    }
}

//------------------------------------------------------
function getOSType(){
    if (stripos(PHP_OS, 'WIN') === 0)
        return "windows";
    else
        return "linux";
}

function getPHPBinaryLocation($os){
    if ($os == "windows")
        return dirname(php_ini_loaded_file()) . DIRECTORY_SEPARATOR . 'php.exe';
    else
        return PHP_BINDIR . DIRECTORY_SEPARATOR . 'php';
}

function isProcessRunning($conn, $os){ //Single instance manager (check if 'our' php cron running)
    $stmt = $conn->prepare("SELECT pid FROM tb_main_cron");
    $stmt->execute();
    $result   = $stmt->get_result();
    $row      = $result->fetch_assoc();
    $prev_pid = $row['pid'];
    
    if ($os == "windows") {
        $handle    = popen("tasklist | findstr php.exe", "r");
        $task_list = fread($handle, 2096);
        if ($task_list) {
            $task_list_arr = explode("\n", $task_list);
            $task_list_arr = array_filter($task_list_arr);
            
            foreach ($task_list_arr as $process) {
                $process_info = array_values(array_filter(explode(" ", $process)));
                try {
                    if ($process_info[1] == $prev_pid) { //Exit if cron running
                        pclose($handle);
                        return true;
                    }
                }
                catch (Exception $e) {
                }
            }
        }
    } else {
        $handle      = popen("ps ax | grep php | awk '{ print $1 }'", "r");
        $process_ids = explode("\n", fread($handle, 2096));
        $process_ids = array_filter($process_ids);

        foreach ($process_ids as $pid) {
            if ($pid == $prev_pid){ //Exit if cron running
                pclose($handle);
                return true;
            }
        }
        pclose($handle);
    }
    return false;
}

function startProcess($os){
    if($os == "windows"){
        pclose(popen("start /b ".getPHPBinaryLocation($os)." SniperPhish_Manager.php quite","r"));    //background execution
    }
    else{        
        pclose(popen(getPHPBinaryLocation($os)." SniperPhish_Manager.php quite &","r"));
    }
}

function executeCron($conn,$os,$campaign_id){
    if($os == "windows")
        pclose(popen("start /b ".getPHPBinaryLocation($os)." mail_campaign_cron.php ".$campaign_id,"r")); //background execution
    else
        pclose(popen(getPHPBinaryLocation($os)." mail_campaign_cron.php ".$campaign_id." &","r"));
}

function isCommandExist($cmd) {
    $handle = popen($cmd, "r");
    $output = fread($handle, 2096);
    pclose($handle);
    return !empty($output);
}
//--------------------------------------
function isTokenValid($conn,$token){
    $stmt = $conn->prepare("SELECT v_hash_time FROM tb_main WHERE v_hash = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    if($result->num_rows > 0){
        $result = $result->fetch_assoc();
        if(time() < $result['v_hash_time'] + 86400*2)
            return true;
    }
    return false;
}
//-----------------------------------------
function shootMail(&$message,$smtp_server,$sender_username,$sender_pwd,$sender_from,$test_to_address,$smtp_enc_level,$cust_headers,$mail_subject,$mail_body,$mail_content_type){
    try {
        $smtp_server_ip = explode(":", $smtp_server)[0];
        $smtp_server_port = explode(":", $smtp_server)[1];
        $sender_from_name = explode("<", $sender_from)[0];
        $sender_from_mail = preg_match("/[\._a-zA-Z0-9-]+@[\._a-zA-Z0-9-]+/i", $sender_from, $matches);
        $sender_from_mail = $matches[0];
        
        if($smtp_enc_level == 0)
            $transport = (new Swift_SmtpTransport($smtp_server_ip, $smtp_server_port)) ->setUsername($sender_username) ->setPassword($sender_pwd);
        else
            $transport = (new Swift_SmtpTransport($smtp_server_ip, $smtp_server_port, $smtp_enc_level==1?"ssl":"tls")) ->setUsername($sender_username) ->setPassword($sender_pwd);

        // Create the Mailer using your created Transport
        $mailer = new Swift_Mailer($transport);

        // configure a message
        $message->setSubject($mail_subject);    
        $message->setFrom([$sender_from_mail => $sender_from_name]);
          $message->setTo([$test_to_address]);
          $message->setBody($mail_body,$mail_content_type);

        $headers = $message->getHeaders();    

        foreach ($cust_headers as $header_name => $header_val) {
            if ($headers->has($header_name)) {            // check if header exist
                if(strcasecmp($header_name, "return-path") == 0)
                    $headers->get('Return-Path')->setAddress($header_val);
                else
                    $headers->get($header_name)->setValue($header_val);
            }
            else{
                if(strcasecmp($header_name, "return-path") == 0)
                    $headers->addPathHeader('Return-Path', $header_val);
                else
                    $headers->addTextHeader($header_name, $header_val);
            }
        }
    
        $result = $mailer->send($message);
        echo json_encode(['result' => 'success']);
    } catch (Exception $e) {
          echo json_encode(['result' => 'failed', 'error' => $e->getMessage()]);
    }
}

//----------------------------------------------------
function getQueryValsFromURL($url){
    $parts =parse_url(html_entity_decode($url), PHP_URL_QUERY);
    parse_str($parts, $query);
    return $query;
}

//---------------------------------------------------------------------------------------
function getServerVariable($conn){
    $result = mysqli_query($conn, "SELECT server_protocol,domain,baseurl FROM tb_main_variables");
        if(mysqli_num_rows($result) > 0){
        return mysqli_fetch_all($result, MYSQLI_ASSOC)[0];
    }
}

function filterKeywords($content,$keyword_vals){
    $keywords = array("{{CID}}", "{{MID}}", "{{NAME}}", "{{FNAME}}", "{{LNAME}}", "{{NOTES}}", "{{EMAIL}}", "{{FROM}}", "{{TRACKINGURL}}", "{{TRACKER}}", "{{BASEURL}}", "{{MUSERNAME}}", "{{MDOMAIN}}");

    foreach($keywords as $keword) 
        $content = str_ireplace($keword,$keyword_vals[$keword],$content);

    preg_match_all('/{{RND\d*}}/i', $content, $matches);
    $matches = array_unique($matches[0]);

    foreach($matches as $keword){
        $length = preg_replace('/\D+/', '', $keword);   //get int. eg: {{RND34}} => 34
        if(!$length)
            $length=5;  //default length 5
        $content = str_ireplace($keword,getRandomStr($length),$content);
    }

    return $content;
}

function filterQRBarCode($content,$keyword_vals,&$message){
    preg_match_all('@src="([^"]+)"@',$content,$matches);
    foreach ($matches[1] as $src_val) {
        $arr_parameters = getQueryValsFromURL($src_val);
        if (array_key_exists('type', $arr_parameters)){
            if(strcasecmp($arr_parameters['type'], "qr_b64") == 0 || strcasecmp($arr_parameters['type'], "bar_b64") == 0){
                $img_b64 = base64_encode(generateQRBarCode($arr_parameters['type'],$arr_parameters['content']));
                $content = str_replace($src_val,"data:image/png;base64,".$img_b64,$content);
            } 
            if(strcasecmp($arr_parameters['type'], "qr_att") == 0 || strcasecmp($arr_parameters['type'], "bar_att") == 0){
                if (array_key_exists('name', $arr_parameters))
                    $fname = $arr_parameters['name'];
                else
                    $fname = "code.png";

                $img_data = generateQRBarCode($arr_parameters['type'],$arr_parameters['content']);
                $file_info = new finfo(FILEINFO_MIME_TYPE);
                $mime_type = $file_info->buffer($img_data);
                $img = $message->embed(new Swift_Image($img_data, $fname, $mime_type));            
                $content = str_replace($src_val,$img,$content);
            } 
        }
    }
    return $content;
}

function generateQRBarCode($type,$img_content){
    ob_start();
    if($type == 'qr_b64' || $type == 'qr_att'){
        $generator = new barcode_generator();
        $options = ['sx'=>5, 'sf'=>5];
        $generator->output_image("png", "qr", $img_content, $options);
        $imagedata = ob_get_clean();
        return $imagedata;
    }
    if($type == 'bar_b64' || $type == 'bar_att'){
        return barcode( "", $img_content, 50, "horizontal", "code128", false, 1);
    }
}

//-----------------Tracker Specific----------------------------
function getIPInfo($conn, $public_ip) {
    $stmt = $conn->prepare("SELECT ip_info FROM tb_data_mailcamp_live WHERE public_ip = ?");
    $stmt->bind_param("s", $public_ip);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    if (empty($result['ip_info'])) {
        $stmt = $conn->prepare("SELECT ip_info FROM tb_data_webpage_visit WHERE public_ip = ?");
        $stmt->bind_param("s", $public_ip);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        if (empty($result['ip_info'])) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:85.0) Gecko/20100101 Firefox/85.0');
            curl_setopt($ch, CURLOPT_URL, "https://ipapi.co/" . $public_ip . "/json/");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $output = json_decode(curl_exec($ch), true);
            return json_encode(craftIPInfoArr($output));
        } else return ($result['ip_info']);
    } else return ($result['ip_info']);
}

function craftIPInfoArr($output){
    $ip_info = [];
    $ip_info['country'] = empty($output['country_name'])?null:$output['country_name'];
    $ip_info['city'] = empty($output['city'])?null:$output['city'];
    $ip_info['zip'] = empty($output['postal'])?null:$output['postal'];
    $ip_info['isp'] = empty($output['org'])?null:$output['org'];
    $ip_info['timezone'] = (empty($output['timezone'])||empty($output['utc_offset']))?null:$output['timezone'].' ('.$output['utc_offset'].')';
    $ip_info['coordinates'] = (empty($output['latitude'])||empty($output['longitude']))?null:$output['latitude'].'(lat)/'.$output['longitude'].'(long)';

    return json_encode($ip_info);
}

function getMailClient($user_agent) {
    $browser        = "unknown";

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

//---------------------------------------------------------------------
function getCampaignDataFromCampaignID($conn, $campaign_id){
    $stmt = $conn->prepare("SELECT campaign_data FROM tb_core_mailcamp_list WHERE campaign_id = ?");
    $stmt->bind_param("s", $campaign_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if($result->num_rows != 0){
        $row = $result->fetch_assoc();
        return json_decode($row["campaign_data"],true);
    }
    else
        return [];
}
//--------------------------------------------------------------------
function doFilter($string, $type){
    if($type == 'ALPHA_NUM')
        return preg_replace("/[^a-zA-Z0-9]+/", '', $string);
    else
    if($type == 'ALPHA')
        return preg_replace("/[^a-zA-Z]+/", '', $string);
    else
    if($type == 'NUM')
        return preg_replace("/[^0-9]+/", '', $string);
    else
        return $string;
}

function getRandomStr($length=10){
    return substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyz', ceil(10/strlen($x)) )),1,intval($length));
}
function getSniperPhishVersion(){
    echo "1.3";
}

?>