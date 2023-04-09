<?php
date_default_timezone_set('UTC');
$entry_time = (new DateTime())->format('d-m-Y h:i A');
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Email;
//-------------------------------------------------------
function checkInstallation(){
    $db_file = dirname(__FILE__,2) . '/config/db.php';
    
    if (file_exists($db_file)) {
        require_once(dirname(__FILE__,2) . '/config/db.php');
        
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
        pclose(popen('start /b '.getPHPBinaryLocation($os).' '.dirname(__FILE__,2).'\core\SniperPhish_Manager.php quite','r'));    //background execution
    }
    else{        
        pclose(popen(getPHPBinaryLocation($os).' '.dirname(__FILE__,2).'/core/SniperPhish_Manager.php quite &','r'));
    }
}

function executeCron($conn,$os,$campaign_id){
    if($os == "windows")
        pclose(popen('start /b '.getPHPBinaryLocation($os).' '.dirname(__FILE__,2).'\core\mail_campaign_cron.php '.$campaign_id,'r')); //background execution
    else
        pclose(popen(getPHPBinaryLocation($os).' '.dirname(__FILE__,2).'/core/mail_campaign_cron.php '.$campaign_id.' &','r'));
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
function shootMail(&$message,$smtp_server,$sender_username,$sender_pwd,$sender_from,$test_to_address,$cust_headers,$mail_subject,$mail_body,$mail_content_type,$dsn_type='custom'){
    try {
        $sender_from_mail = preg_match("/[\._a-zA-Z0-9-]+@[\._a-zA-Z0-9-]+/i", $sender_from, $matches);
        $sender_from_mail = $matches[0];
        $sender_from_name = explode("<", $sender_from)[0];

        $transport = Transport::fromDsn(getMailerDSN($dsn_type, urlencode($sender_username), urlencode($sender_pwd), $smtp_server, 0));
        $mailer = new Mailer($transport);
        $message->from($sender_from)->to($test_to_address)->subject($mail_subject);

        if($mail_content_type == 'text/html')
            $message->html($mail_body);
        else
            $message->text($mail_body);

        foreach ($cust_headers as $header_name => $header_val) {
            if(strcasecmp($header_name, 'return-path') == 0)
                $message->returnPath($header_val);
            elseif(strcasecmp($header_name, 'reply-to') == 0)
                $message->replyTo($header_val);
            else
                $message->getHeaders()->addTextHeader($header_name, $header_val);
        }

        $mailer->send($message);
        echo json_encode(['result' => 'success']);
    } catch (Exception $e) {
          echo json_encode(['result' => 'failed', 'error' => $e->getMessage()]);
    }
}

function getMailerDSN($dsn_type, $sender_username, $sender_pwd, $smtp_server, $verify_peer=0){
    $dsn_type=strtolower($dsn_type);
    switch($dsn_type){
        case 'amazon_ses'           : return 'ses+smtp://'.$sender_username.':'.$sender_pwd.'@default?verify_peer='.$verify_peer;
        case 'gmail'                : return 'gmail+smtp://'.$sender_username.':'.$sender_pwd.'@default?verify_peer='.$verify_peer;
        case 'mailchimp_mandrill'   : return 'mandrill+smtp://'.$sender_username.':'.$sender_pwd.'@default?verify_peer='.$verify_peer;
        case 'mailgun'              : return 'mailgun+smtp://'.$sender_username.':'.$sender_pwd.'@default?verify_peer='.$verify_peer;
        case 'mailjet'              : return 'mailjet+smtp://'.$sender_username.':'.$sender_pwd.'@default?verify_peer='.$verify_peer;   //mailjet+smtp://ACCESS_KEY:SECRET_KEY@default
        case 'postmark'             : return 'postmark+smtp://'.$sender_pwd.'@default?verify_peer='.$verify_peer;   //postmark+smtp://ID@default
        case 'sendgrid'             : return 'sendgrid+smtp://'.$sender_pwd.'@default?verify_peer='.$verify_peer;  //sendgrid+smtp://KEY@default
        case 'sendinblue'           : return 'sendinblue+smtp://'.$sender_username.':'.$sender_pwd.'@default?verify_peer='.$verify_peer;
        case 'mailpace'             : return 'mailpace+api://'.$sender_pwd.'@default?verify_peer='.$verify_peer;    //mailpace+api://API_TOKEN@default
        default                     : return 'smtp://'.$sender_username.':'.$sender_pwd.'@'.$smtp_server.'?verify_peer='.$verify_peer;
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

function setServerVariables($conn){
    $server_protocol = isset($_SERVER['HTTPS'])?'https':'http';
    $baseurl = $server_protocol.'://'.$_SERVER['HTTP_HOST'];
    $stmt = $conn->prepare("UPDATE tb_main_variables SET server_protocol=?, domain =?, baseurl=?");
    $stmt->bind_param('sss', $server_protocol, $_SERVER['HTTP_HOST'], $baseurl);
    
    $stmt->execute();
    $stmt->close();
}

function filterKeywords($content,$keyword_vals){
    $keywords = array("{{RID}}", "{{MID}}", "{{NAME}}", "{{FNAME}}", "{{LNAME}}", "{{NOTES}}", "{{EMAIL}}", "{{FROM}}", "{{TRACKINGURL}}", "{{TRACKER}}", "{{BASEURL}}", "{{MUSERNAME}}", "{{MDOMAIN}}");

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
                $message->embed($img_data, $fname, $mime_type);   
                $content = str_replace($src_val,'cid:'.$fname,$content);//cid:filename embeds image
            } 
        }
    }
    return $content;
}

function generateQRBarCode($type,$img_content){
    ob_start(); // required to avoid error stopping campaign (erro visible if campagn is run from terminal )
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

function checkAnIDExist($conn,$id_value,$id_name,$table_name){
    $stmt = $conn->prepare("SELECT COUNT(*) FROM $table_name WHERE $id_name=?");
    $stmt->bind_param("s", $id_value);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_row();
    if($row[0] > 0)
        return true;
    else
        return false;
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

    return $ip_info;
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

function getPublicIP(){
    $public_ip = getenv('HTTP_CLIENT_IP')?:
    getenv('HTTP_X_FORWARDED_FOR')?:
    getenv('HTTP_X_FORWARDED')?:
    getenv('HTTP_FORWARDED_FOR')?:
    getenv('HTTP_FORWARDED')?:
    getenv('REMOTE_ADDR');
    return htmlspecialchars($public_ip);
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
function getTimelineDataMail($conn, $campaign_id, $DTime_info){
    $scatter_data_mail = $timestamp_conv = [];
    $mail_open_count = 0;

    $stmt = $conn->prepare("SELECT sending_status,send_time,user_name,user_email,mail_open_times FROM tb_data_mailcamp_live WHERE campaign_id=?");
    $stmt->bind_param("s", $campaign_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $rows = $result->fetch_all(MYSQLI_ASSOC);
    foreach($rows as $i => $row){
        $timestamp_conv[$row['send_time']] = getInClientTime($DTime_info,$row['send_time']);
        $row['mail_open_times'] = json_decode($row['mail_open_times']);
        if($row['mail_open_times']){
            $mail_open_count++;
            foreach($row['mail_open_times'] as $timestamp)
                $timestamp_conv[$timestamp] = getInClientTime($DTime_info,$timestamp);
        }
        array_push($scatter_data_mail,$row);
    }
    return ['scatter_data_mail'=>$scatter_data_mail, 'timestamp_conv'=>$timestamp_conv, 'mail_open_count'=>$mail_open_count];
}

function getMailReplied($conn, $campaign_id, $quite=false){
    session_write_close(); //Required to avoid hanging by executing this fun
    $arr_replied_mails = [];
    $arr_err = [];

    $campaign_data = getCampaignDataFromCampaignID($conn, $campaign_id);
    $sender_list_id = $campaign_data['mail_sender']['id'];
    $user_group_id = $campaign_data['user_group']['id'];

    $stmt = $conn->prepare("SELECT sender_name,sender_SMTP_server,sender_from,sender_acc_username,sender_acc_pwd,sender_mailbox,cust_headers FROM tb_core_mailcamp_sender_list WHERE sender_list_id = ?");
    $stmt->bind_param("s", $sender_list_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if($result->num_rows > 0){
        $row = $result->fetch_assoc() ;
        $sender_username = $row['sender_acc_username'];
        $sender_acc_pwd = $row['sender_acc_pwd'];
        $sender_mailbox = $row['sender_mailbox'];

        //------------------Get mail subject---------
        $stmt = $conn->prepare("SELECT rid FROM tb_data_mailcamp_live WHERE campaign_id = ?");
        $stmt->bind_param("s", $campaign_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $RIDs = [];
        while($row = $result->fetch_assoc())
            array_push($RIDs,$row['rid']);
        
        //-----------
        $arr_msg_info =[];

        try{
            if($read = imap_open($sender_mailbox,$sender_username,$sender_acc_pwd)){             
                $array = imap_search($read,'TEXT "@spmailer.generated"'); // match for Message-ID header {{RID}}@spmailer.generated
                foreach($array as $result) {
                    $overview = imap_fetch_overview($read,$result,0); //var_dump($overview[0]->references);
                    if($overview[0]->references == NULL)    
                        $tmp = explode("@spmailer.generated",$overview[0]->in_reply_to)[0]; //check reply mail header in_reply_to
                    else
                        $tmp = explode("@spmailer.generated",$overview[0]->references)[0]; //check reply mail header references
                    $header_to_check = explode("<",$tmp)[1];    //xxx {{RID}}@spmailer.generated> => {{RID}} 

                    //get email address part only
                    if (filter_var($overview[0]->from, FILTER_VALIDATE_EMAIL))
                        $msg_from = $overview[0]->from;
                    else
                        $msg_from = str_ireplace(">","",explode("<",$overview[0]->from)[1]);    //xxx <username@domain.com> => username@domain.com 

                    if(in_array($header_to_check, $RIDs)){
                        $msg_time = $overview[0]->date;         
                        $msg_body = imap_fetchbody ($read,$result,1);
                        if (!array_key_exists($msg_from, $arr_msg_info))
                            $arr_msg_info[$msg_from] = ['msg_time'=>[$msg_time],'msg_body'=>[$msg_body]];
                        else{
                            array_push($arr_msg_info[$msg_from]['msg_time'],$msg_time);
                            array_push($arr_msg_info[$msg_from]['msg_body'],$msg_body);
                        }   
                    }
                }
            }
        }catch(Exception $e) {
            array_push($arr_err,$e->getMessage());
        }
        array_push($arr_err,imap_errors());     //required to capture imap errors
        
        if(empty($arr_err) || $arr_err[0] == false)
            if($quite)
                return ['reply_count_unique'=>count($arr_msg_info), 'msg_info'=>$arr_msg_info];
            else
                echo json_encode(['reply_count_unique'=>count($arr_msg_info), 'msg_info'=>$arr_msg_info]);
        else
            if($quite)
                return ['error'=>$arr_err, 'reply_count_unique'=>count($arr_msg_info), 'msg_info'=>$arr_msg_info];
            else
                echo json_encode(['error'=>$arr_err, 'reply_count_unique'=>count($arr_msg_info), 'msg_info'=>$arr_msg_info]);
    }           
    $stmt->close();
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

function isValidEmail($email) { //supports  RFC 5322
  if (empty($email)) {
    return false;
  }

  $email = mb_convert_encoding($email, 'UTF-8', 'auto');
  $regex = '/^[a-zA-Z0-9!#$%&\'*+\/=?^_`{|}~.-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/';
  
  return filter_var($email, FILTER_VALIDATE_EMAIL) || preg_match($regex, $email);
}

function getTimeInfo($conn){    //get client-set date-time formats
    $result = mysqli_query($conn, "SELECT time_zone,time_format FROM tb_main_variables")->fetch_assoc();
    $result['time_zone'] = json_decode($result['time_zone'],true);
    $result['time_format'] = json_decode($result['time_format'],true);

    if($result['time_format']['space'] == 'comma')
        $sep = ',';
    elseif ($result['time_format']['space'] == 'comaspace')
        $sep = ', ';
    else
        $sep = ' ';
    $result['date_time_format'] = $result['time_format']['date'].$sep.$result['time_format']['time'];
    return $result;
}

function getInClientTime($DTime_info,$timestamp,$time_zone=null,$date_time_format=null){    //Get in client set format from microseconds timestamp
    if($timestamp==null || $timestamp=='-')
        return '-';

    if($time_zone == null)
        $time_zone = $DTime_info['time_zone']['timezone'];
    if($date_time_format == null)
        $date_time_format = $DTime_info['date_time_format'];

    if($DTime_info['time_format']['date'] == 'Unix Timestamp-seconds')
        return round($timestamp/1000);
    elseif($DTime_info['time_format']['date'] == 'Unix Timestamp-milliseconds')
        return $timestamp;
    else
        return DateTime::createFromFormat('U.u', number_format($timestamp/1000, 6, '.', ''))->setTimeZone(new DateTimeZone($time_zone))->format($date_time_format); 
        //eg: $date = DateTime::createFromFormat('U.u', number_format(1618225519512/1000, 6, '.', ''))->setTimeZone(new DateTimeZone('Asia/Kuala_Lumpur'))->format("d-m-y H:i:s.u");
}

function getInClientTime_FD($DTime_info, $in_date, $time_zone=null,$out_date_time_format=null){   //Standard date-time format to specified format; FD=Formatted Date
    if($in_date==null || $in_date=='-')
        return '-';

    if($time_zone == null)
        $time_zone = $DTime_info['time_zone']['timezone'];
    if($out_date_time_format == null)
        $out_date_time_format = $DTime_info['date_time_format'];

    if($DTime_info['time_format']['date'] == 'Unix Timestamp-seconds')
        return DateTime::createFromFormat('d-m-Y h:i A', $in_date)->getTimestamp();
    elseif($DTime_info['time_format']['date'] == 'Unix Timestamp-milliseconds')
        return DateTime::createFromFormat('d-m-Y h:i A', $in_date)->format('Uv');
    else
        return DateTime::createFromFormat('d-m-Y h:i A', $in_date)->setTimeZone(new DateTimeZone($time_zone))->format($out_date_time_format); //eg: 03-02-2022 05:23 PM in UTC to 03-02-2022 10:53 PM in IST; 'd-m-Y h:i A' is fixed DB time value format
}

function getTimeInUnix($DTime_info, $in_date, $date_time_format=null){
    if($DTime_info == null)
        $date_time_format = 'd-m-Y h:i A'; //fixed DB time value format
    else{
        if($date_time_format == null)
            $date_time_format = $DTime_info['date_time_format'];
    }

    if($in_date==null || $in_date=='-')
        return (new DateTime(null))->getTimestamp();
    else
        return DateTime::createFromFormat($date_time_format, $in_date)->getTimestamp(); //eg: 06-04-2021 05:21 PM in UTC to 1617709860 unix timestamp
}

function getHTMLData(&$arr_odata,&$file_name,&$selected_col,&$dic_all_col){
    $html_data='<style>table {
      border-collapse: collapse;              
    }
    table th {
        font-weight: bold;
        border: 1px solid #ddd;white-space: nowrap;
    }
    table td {
      border: 1px solid #ddd;white-space: nowrap;
    }
    .alt_r_style {
        background-color: #f2f2f2;
    }
    </style>
    <h1>'.$file_name.'</h1>
    <table class="first"><tr><th>SN</th>';
    foreach ($selected_col as $col)
        if(array_key_exists($col,$dic_all_col))
            $html_data .='<th >'.$dic_all_col[$col].'</th>';
        else
            $html_data .='<th >'.$col.'</th>';
        
    $html_data .='</tr>';

    foreach ($arr_odata as $i=>$row){
        if($i%2==0)
            $html_data .='<tr class="alt_r_style">';
        else
            $html_data .='<tr>';

        $html_data .='<td>'.($i+1).'</td>';

        foreach ($row as $item)
            $html_data .='<td>'.$item.'</td>';
        $html_data .='</tr>';
    }
    $html_data .='</table>';
    return $html_data;
}
//--------------Logger--------
function logIt($log,$username=null){
    global $conn;
    $username=$username==null?$_SESSION['username']:$username;
    $entry_time=$GLOBALS['entry_time'];
    $public_ip = getPublicIP();

    $stmt = $conn->prepare("INSERT INTO tb_log(username,log,ip,date) VALUES(?,?,?,?)");
    $stmt->bind_param('ssss', $username,$log,$public_ip,$entry_time);
    $stmt->execute();
    $stmt->close();
}

function getRandomStr($length=10){
    return substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyz', ceil(10/strlen($x)) )),1,intval($length));
}
function getSniperPhishVersion(){   //update this when new version releases
    echo "2.1";
}

?>