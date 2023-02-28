<?php
error_reporting(E_ALL ^ E_WARNING); //display error but not warnings
ini_set('display_errors', true);    //display error on screen
require_once (dirname(__FILE__) . '/spear/manager/common_functions.php');
header('Content-Type: application/json');
date_default_timezone_set('UTC');
$entry_time = (new DateTime())->format('d-m-Y h:i A');

if (isset($_POST))
    $POSTJ = json_decode(file_get_contents('php://input'),true);
else 
    die();

if (isset($POSTJ['action_type'])){
    if ($POSTJ['action_type'] == "check_requirements") 
        checkRequirements();
  if ($POSTJ['action_type'] == "do_install")
        doInstall($POSTJ);
}

//----------------------------------------------------------------------
function checkRequirements(){
    $resp_arr = [];
    $f_error = false;
    $extensions = get_loaded_extensions();
    $success = "<i class='fas fa-check fa-lg text-success'></i>";

    if (version_compare(phpversion(), '8.1', '>=')) 
        $resp_arr['PHP version ' . phpversion() ] = true;
    else{
        $resp_arr['PHP version ' . phpversion() ] = "PHP version >= 8.1 is required"; 
        $f_error = true;
    }
    
    if (in_array("mysqli", $extensions)) 
        $resp_arr['PHP extension mysqli'] = true;
    else{
        $resp_arr['PHP extension mysqli'] = "mysqli extension should be loaded";
        $f_error = true;
    }

    if (in_array("imap", $extensions)) 
        $resp_arr['PHP extension imap'] = true;
    else{
        $resp_arr['PHP extension imap'] = "Imap extension should be loaded";
        $f_error = true;
    }

    if (in_array("gd", $extensions)) 
        $resp_arr['PHP extension gd'] = true;
    else{
        $resp_arr['PHP extension gd'] = "gd extension should be loaded";
        $f_error = true;
    }

    $permission_info = getWritePermissionInfo();
    if(!empty($permission_info))
      $f_error = true;

    //SP process commands check
    if(getOSType() == 'windows'){
      if(isCommandExist('tasklist') == false){
        $resp_arr['Command - tasklist'] = "'tasklist' command should be enabled in your system";
        $f_error = true;
      }
      if(isCommandExist('findstr /?') == false){
        $resp_arr['Command - findstr'] = "'findstr' command should be enabled in your system";
        $f_error = true;
      }
    }
    else{
      if(isCommandExist('ps a') == false){
        $resp_arr['Command - ps'] = "'ps' command should be enabled in your system";
        $f_error = true;
      }
      if(isCommandExist('grep --help') == false){
        $resp_arr['Command - grep'] = "'grep' command should be enabled in your system";
        $f_error = true;
      }
      if(isCommandExist('awk --help') == false){
        $resp_arr['Command - awk'] = "'awk' command should be enabled in your system";
        $f_error = true;
      }
    }

    echo json_encode(['error' => $f_error, 'requirements' => $resp_arr, 'permissions' => $permission_info]);    
}

function getWritePermissionInfo(){
    $resp = [];
    
    if (!is_writable(dirname(__FILE__).'/spear/config'))  //for db.php
        array_push($resp,dirname(__FILE__).'/spear');

    if (!is_writable(dirname(__FILE__).'/spear/uploads'))   //for uploads w.r.t mail
        array_push($resp,dirname(__FILE__).'/spear/uploads');

    if (is_dir(dirname(__FILE__).'/spear/payloads/uploads') && !is_writable(dirname(__FILE__).'/spear/payloads/uploads'))   //for payloads
        array_push($resp,dirname(__FILE__).'/spear/payloads/uploads');
    if (!is_writable(dirname(__FILE__).'/spear/sniperhost/hf_files'))   //for host files
        array_push($resp,dirname(__FILE__).'/spear/sniperhost/hf_files');
    if (!is_writable(dirname(__FILE__).'/spear/sniperhost/ht_files'))   //for host text files
        array_push($resp,dirname(__FILE__).'/spear/sniperhost/ht_files');
    if (!is_writable(dirname(__FILE__).'/spear/sniperhost/ht_files'))   //for landing page files
        array_push($resp,dirname(__FILE__).'/spear/sniperhost/lp_pages');

    return $resp;
}

function doInstall(&$POSTJ){
    checkInstallation(); //check installation
    $db_name = $POSTJ['db_name'];
    $db_host = $POSTJ['db_host'];
    $db_user_name = $POSTJ['db_user_name'];
    $db_user_pwd = $POSTJ['db_user_pwd'];
    $user_contact_mail = $POSTJ['user_contact_mail'];
    $time_zone = json_encode($POSTJ['time_zone']);
    $f_force = $POSTJ['f_force'];

    $conn = mysqli_connect($db_host, $db_user_name, $db_user_pwd, $db_name);

    if (mysqli_connect_errno())
        echo json_encode(['error' => "Connection failed: " . mysqli_connect_error()]);
    else
    {
        $file_contents = '<?php
  $curr_db = "'.$db_name.'";
  $conn = mysqli_connect("'.$db_host.'","'.$db_user_name.'","'.$db_user_pwd.'",$curr_db);

  if (mysqli_connect_errno()) {
    die("DB connection failed!");
  } 
?>';

        $result = mysqli_query($conn, 'SHOW TABLES');
        if($f_force == true){ //drop DB tables
            if ($result){
                while($row = $result->fetch_array(MYSQLI_NUM))
                    mysqli_query($conn,'DROP TABLE IF EXISTS '.$row[0]);
            }
        }
        else{
            if(mysqli_num_rows($result) > 0)
                die(json_encode(['error' => 'db_content_exist']));
        }
        

        if(file_put_contents('spear/config/db.php', $file_contents)){ //created db.php file
            if (createTables($conn)) //creates SniperPhish DB tables
                if (modifySniperPhishSettings($conn, $time_zone, $user_contact_mail))
                    echo json_encode(['result' => 'success']);  
                else 
                    echo json_encode(['error' => "Error in timezone update"]);
            else
                echo json_encode(['error' => "Error in creating database tables! Retry or empty the database."]);
        }
        else
            echo json_encode(['error' => "Error writing file spear/db.php"]);

        setServerVariables($conn);
    }
}

function modifySniperPhishSettings($conn, $time_zone, $user_contact_mail){   
    $def_time_format = '{"date":"d-m-o","space":"comaspace","time":"h:i:s:v A"}';
    $stmt = $conn->prepare("INSERT INTO tb_main_variables(id,time_zone,time_format) VALUES(1,?,?)");
    $stmt->bind_param('ss', $time_zone,$def_time_format);
    if ($stmt->execute() === FALSE)
        return false;
    $stmt->close(); 
      
    $stmt = $conn->prepare("INSERT INTO tb_main(id,name,username,password,contact_mail,dp_name,date) VALUES(1,'Admin','admin','23d119e1749d0d0f21dd751c52d3ca221462867669acaf58f209aa237a3955a3',?,1,?)");
    $stmt->bind_param('ss', $user_contact_mail,$GLOBALS['entry_time']);
    if ($stmt->execute() === TRUE)
        return true;
    else
        return false;
}

function createTables($conn){
    $tables = <<<'EOD'
-- phpMyAdmin SQL Dump
-- version 5.0.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 16, 2022 at 07:31 PM
-- Server version: 10.4.11-MariaDB
-- PHP Version: 7.4.5

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `t1`
--

-- --------------------------------------------------------

--
-- Table structure for table `tb_access_ctrl`
--

CREATE TABLE `tb_access_ctrl` (
  `tk_id` varchar(11) NOT NULL,
  `ctrl_ids` varchar(111) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `tb_core_mailcamp_config`
--

CREATE TABLE `tb_core_mailcamp_config` (
  `mconfig_id` varchar(50) NOT NULL,
  `mconfig_name` varchar(50) DEFAULT NULL,
  `mconfig_data` mediumtext DEFAULT NULL,
  `date` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `tb_core_mailcamp_list`
--

CREATE TABLE `tb_core_mailcamp_list` (
  `campaign_id` varchar(50) NOT NULL,
  `campaign_name` varchar(50) NOT NULL,
  `campaign_data` varchar(1111) NOT NULL,
  `date` varchar(111) NOT NULL,
  `scheduled_time` varchar(111) NOT NULL,
  `stop_time` varchar(111) DEFAULT NULL,
  `camp_status` int(11) NOT NULL DEFAULT 0,
  `camp_lock` tinyint(4) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tb_core_mailcamp_sender_list`
--

CREATE TABLE `tb_core_mailcamp_sender_list` (
  `sender_list_id` varchar(111) NOT NULL,
  `sender_name` varchar(50) NOT NULL,
  `sender_smtp_server` varchar(50) NOT NULL,
  `sender_from` varchar(111) NOT NULL,
  `sender_acc_username` varchar(111) NOT NULL,
  `sender_acc_pwd` varchar(50) NOT NULL,
  `auto_mailbox` tinyint(1) NOT NULL DEFAULT 0,
  `sender_mailbox` varchar(1111) DEFAULT NULL,
  `cust_headers` varchar(1111) DEFAULT NULL,
  `dsn_type` varchar(111) DEFAULT NULL,
  `date` varchar(111) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tb_core_mailcamp_template_list`
--

CREATE TABLE `tb_core_mailcamp_template_list` (
  `mail_template_id` varchar(111) NOT NULL,
  `mail_template_name` varchar(111) DEFAULT NULL,
  `mail_template_subject` varchar(1111) DEFAULT NULL,
  `mail_template_content` mediumtext DEFAULT NULL,
  `timage_type` tinyint(1) NOT NULL DEFAULT 0,
  `mail_content_type` varchar(111) DEFAULT '{}',
  `attachment` varchar(1111) DEFAULT NULL,
  `date` varchar(111) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tb_core_mailcamp_user_group`
--

CREATE TABLE `tb_core_mailcamp_user_group` (
  `user_group_id` varchar(111) NOT NULL,
  `user_group_name` varchar(50) NOT NULL,
  `user_data` mediumtext DEFAULT NULL,
  `date` varchar(111) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tb_core_quick_tracker_list`
--

CREATE TABLE `tb_core_quick_tracker_list` (
  `tracker_id` varchar(11) NOT NULL,
  `tracker_name` varchar(111) NOT NULL,
  `date` varchar(111) NOT NULL,
  `start_time` varchar(111) DEFAULT NULL,
  `stop_time` varchar(111) DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tb_core_web_tracker_list`
--

CREATE TABLE `tb_core_web_tracker_list` (
  `tracker_id` varchar(111) NOT NULL,
  `tracker_name` varchar(111) NOT NULL,
  `content_html` varchar(1111) DEFAULT NULL,
  `content_js` varchar(11111) DEFAULT NULL,
  `tracker_step_data` mediumtext DEFAULT NULL,
  `date` varchar(111) DEFAULT NULL,
  `start_time` varchar(111) DEFAULT NULL,
  `stop_time` varchar(111) DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tb_data_mailcamp_live`
--

CREATE TABLE `tb_data_mailcamp_live` (
  `rid` varchar(15) NOT NULL,
  `campaign_id` varchar(15) DEFAULT NULL,
  `campaign_name` varchar(50) DEFAULT NULL,
  `sending_status` tinyint(11) NOT NULL DEFAULT 0,
  `send_time` varchar(50) DEFAULT NULL,
  `user_name` varchar(50) DEFAULT NULL,
  `user_email` varchar(111) DEFAULT NULL,
  `send_error` varchar(1111) DEFAULT NULL,
  `mail_open_times` mediumtext DEFAULT NULL,
  `public_ip` mediumtext DEFAULT NULL,
  `ip_info` mediumtext DEFAULT NULL,
  `user_agent` mediumtext DEFAULT NULL,
  `mail_client` mediumtext DEFAULT NULL,
  `platform` mediumtext DEFAULT NULL,
  `device_type` mediumtext DEFAULT NULL,
  `all_headers` mediumtext DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `tb_data_quick_tracker_live`
--

CREATE TABLE `tb_data_quick_tracker_live` (
  `id` int(111) NOT NULL,
  `tracker_id` varchar(111) DEFAULT NULL,
  `rid` varchar(111) DEFAULT NULL,
  `public_ip` varchar(111) DEFAULT NULL,
  `ip_info` varchar(2222) DEFAULT NULL,
  `user_agent` varchar(222) DEFAULT NULL,
  `mail_client` varchar(222) DEFAULT NULL,
  `platform` varchar(222) DEFAULT NULL,
  `all_headers` varchar(2222) DEFAULT NULL,
  `time` varchar(222) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tb_data_webform_submit`
--

CREATE TABLE `tb_data_webform_submit` (
  `id` int(11) NOT NULL,
  `tracker_id` varchar(111) DEFAULT NULL,
  `rid` varchar(222) DEFAULT NULL,
  `session_id` varchar(222) DEFAULT NULL,
  `public_ip` varchar(222) DEFAULT NULL,
  `ip_info` varchar(2222) DEFAULT NULL,
  `user_agent` varchar(222) DEFAULT NULL,
  `screen_res` varchar(22) DEFAULT NULL,
  `time` varchar(222) DEFAULT NULL,
  `browser` varchar(222) DEFAULT NULL,
  `platform` varchar(222) DEFAULT NULL,
  `device_type` varchar(11) DEFAULT NULL,
  `page` int(111) DEFAULT NULL,
  `form_field_data` varchar(22222) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tb_data_webpage_visit`
--

CREATE TABLE `tb_data_webpage_visit` (
  `id` int(11) NOT NULL,
  `tracker_id` varchar(111) DEFAULT NULL,
  `rid` varchar(222) DEFAULT NULL,
  `session_id` varchar(222) DEFAULT NULL,
  `public_ip` varchar(222) DEFAULT NULL,
  `ip_info` varchar(2222) DEFAULT NULL,
  `user_agent` varchar(222) DEFAULT NULL,
  `screen_res` varchar(22) DEFAULT NULL,
  `time` varchar(222) DEFAULT NULL,
  `browser` varchar(222) DEFAULT NULL,
  `platform` varchar(222) DEFAULT NULL,
  `device_type` varchar(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tb_hf_list`
--

CREATE TABLE `tb_hf_list` (
  `hf_id` varchar(11) NOT NULL,
  `hf_name` varchar(111) NOT NULL,
  `file_original_name` varchar(111) DEFAULT NULL,
  `file_header` varchar(111) NOT NULL,
  `date` varchar(111) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `tb_hland_page_list`
--

CREATE TABLE `tb_hland_page_list` (
  `hlp_id` varchar(11) NOT NULL,
  `page_name` mediumtext DEFAULT NULL,
  `page_file_name` varchar(111) DEFAULT NULL,
  `date` varchar(111) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `tb_ht_list`
--

CREATE TABLE `tb_ht_list` (
  `ht_id` varchar(11) NOT NULL,
  `ht_name` varchar(111) DEFAULT NULL,
  `alg` varchar(1111) DEFAULT NULL,
  `file_extension` varchar(111) DEFAULT NULL,
  `file_header` varchar(111) DEFAULT NULL,
  `date` varchar(111) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `tb_log`
--

CREATE TABLE `tb_log` (
  `id` int(11) NOT NULL,
  `username` varchar(111) DEFAULT NULL,
  `log` text DEFAULT NULL,
  `ip` varchar(55) DEFAULT NULL,
  `date` varchar(111) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `tb_main`
--

CREATE TABLE `tb_main` (
  `id` int(11) NOT NULL,
  `name` varchar(111) DEFAULT NULL,
  `username` varchar(111) DEFAULT NULL,
  `password` varchar(222) DEFAULT NULL,
  `contact_mail` varchar(111) DEFAULT NULL,
  `dp_name` varchar(111) DEFAULT NULL,
  `v_hash` varchar(111) DEFAULT NULL,
  `v_hash_time` varchar(111) DEFAULT NULL,
  `date` varchar(111) DEFAULT NULL,
  `last_login` varchar(111) DEFAULT NULL,
  `last_logout` varchar(111) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tb_main_cron`
--

CREATE TABLE `tb_main_cron` (
  `id` int(11) NOT NULL,
  `pid` int(111) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `tb_main_variables`
--

CREATE TABLE `tb_main_variables` (
  `id` int(1) NOT NULL,
  `server_protocol` varchar(11) DEFAULT NULL,
  `domain` varchar(111) DEFAULT NULL,
  `baseurl` varchar(111) DEFAULT NULL,
  `time_zone` varchar(111) DEFAULT NULL,
  `time_format` varchar(222) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `tb_pl_list`
--

CREATE TABLE `tb_pl_list` (
  `pl_id` varchar(11) NOT NULL,
  `pl_name` varchar(111) DEFAULT NULL,
  `file_name` varchar(111) DEFAULT NULL,
  `pl_type` varchar(111) DEFAULT NULL,
  `pl_sub_type` varchar(111) DEFAULT NULL,
  `date` varchar(111) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `tb_store`
--

CREATE TABLE `tb_store` (
  `type` varchar(111) DEFAULT NULL,
  `name` varchar(111) NOT NULL,
  `info` varchar(700) DEFAULT NULL,
  `content` mediumtext DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tb_access_ctrl`
--
ALTER TABLE `tb_access_ctrl`
  ADD PRIMARY KEY (`tk_id`);

--
-- Indexes for table `tb_core_mailcamp_config`
--
ALTER TABLE `tb_core_mailcamp_config`
  ADD PRIMARY KEY (`mconfig_id`);

--
-- Indexes for table `tb_core_mailcamp_list`
--
ALTER TABLE `tb_core_mailcamp_list`
  ADD PRIMARY KEY (`campaign_id`),
  ADD UNIQUE KEY `id` (`campaign_id`);

--
-- Indexes for table `tb_core_mailcamp_sender_list`
--
ALTER TABLE `tb_core_mailcamp_sender_list`
  ADD PRIMARY KEY (`sender_list_id`),
  ADD UNIQUE KEY `sender_list_id` (`sender_list_id`);

--
-- Indexes for table `tb_core_mailcamp_template_list`
--
ALTER TABLE `tb_core_mailcamp_template_list`
  ADD PRIMARY KEY (`mail_template_id`);

--
-- Indexes for table `tb_core_mailcamp_user_group`
--
ALTER TABLE `tb_core_mailcamp_user_group`
  ADD PRIMARY KEY (`user_group_id`);

--
-- Indexes for table `tb_core_quick_tracker_list`
--
ALTER TABLE `tb_core_quick_tracker_list`
  ADD PRIMARY KEY (`tracker_id`);

--
-- Indexes for table `tb_core_web_tracker_list`
--
ALTER TABLE `tb_core_web_tracker_list`
  ADD PRIMARY KEY (`tracker_id`),
  ADD UNIQUE KEY `id_2` (`tracker_id`),
  ADD KEY `id` (`tracker_id`),
  ADD KEY `id_3` (`tracker_id`);

--
-- Indexes for table `tb_data_mailcamp_live`
--
ALTER TABLE `tb_data_mailcamp_live`
  ADD PRIMARY KEY (`rid`);

--
-- Indexes for table `tb_data_quick_tracker_live`
--
ALTER TABLE `tb_data_quick_tracker_live`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tb_data_webform_submit`
--
ALTER TABLE `tb_data_webform_submit`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tb_data_webpage_visit`
--
ALTER TABLE `tb_data_webpage_visit`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tb_hf_list`
--
ALTER TABLE `tb_hf_list`
  ADD PRIMARY KEY (`hf_id`);

--
-- Indexes for table `tb_hland_page_list`
--
ALTER TABLE `tb_hland_page_list`
  ADD PRIMARY KEY (`hlp_id`);

--
-- Indexes for table `tb_ht_list`
--
ALTER TABLE `tb_ht_list`
  ADD PRIMARY KEY (`ht_id`);

--
-- Indexes for table `tb_log`
--
ALTER TABLE `tb_log`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tb_main`
--
ALTER TABLE `tb_main`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tb_main_cron`
--
ALTER TABLE `tb_main_cron`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tb_main_variables`
--
ALTER TABLE `tb_main_variables`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tb_pl_list`
--
ALTER TABLE `tb_pl_list`
  ADD PRIMARY KEY (`pl_id`);

--
-- Indexes for table `tb_store`
--
ALTER TABLE `tb_store`
  ADD PRIMARY KEY (`name`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tb_data_quick_tracker_live`
--
ALTER TABLE `tb_data_quick_tracker_live`
  MODIFY `id` int(111) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tb_data_webform_submit`
--
ALTER TABLE `tb_data_webform_submit`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tb_data_webpage_visit`
--
ALTER TABLE `tb_data_webpage_visit`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tb_log`
--
ALTER TABLE `tb_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tb_main`
--
ALTER TABLE `tb_main`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tb_main_cron`
--
ALTER TABLE `tb_main_cron`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;






EOD;

$tables .= <<<'EOD'
--
-- Dumping data for table `tb_main_cron`
--

INSERT INTO `tb_main_cron` (`id`, `pid`) VALUES
(1, 0);
-- --------------------------------------------------------

--
-- Dumping data for table `tb_store`
--
INSERT INTO `tb_store` (`type`, `name`, `info`, `content`) VALUES
('mail_sender', 'Amazon SES', '{\"dsn_type\":\"amazon_ses\",\"disp_note\":\"\"}', '{\"from\":\"\",\"username\":\"\",\"mailbox\":{\"value\":\"\",\"disabled\":false,\"checked\":false},\"smtp\":{\"value\":\"NA\",\"disabled\":true}}'),
('mail_sender', 'Custom', '{\"dsn_type\":\"custom\",\"disp_note\":\"Note: Custom mail sender template\"}', '{\"from\":\"\",\"username\":\"\",\"mailbox\":{\"value\":\"\",\"disabled\":false,\"checked\":false},\"smtp\":{\"value\":\"\",\"disabled\":false}}'),
('mail_template', 'Give me your address', '{\"disp_note\":\"Desc: A simple mail to track mail open and capture data from the phishing site\"}', '{\"mail_template_subject\":\"Free COVID-19 Vaccine for {{FNAME}}\",\"mail_template_content\":\"Dear Sir\\/Madam<br><br>We are happy to inform you that you have been selected to receive the COVID-19 vaccine at your home for free. Please submit your address in the link given below, so that we can arrange our medical representative.<br><br>Submit address <a href=\\\"https:\\/\\/yourphishing site.com\\/form.html?rid={{RID}}\\\">here<\\/a><br><br>Please let us know if you have any questions.<br><br>Regards,<br>Cage,<br>Chief Medical Officer<br><br>{{TRACKER}}\",\"timage_type\":1,\"mail_content_type\":\"text/html\",\"attachment\":[]}'),
('mail_sender', 'Gmail (gmail.com)', '{\"dsn_type\":\"gmail\",\"disp_note\":\"Note: You need to create app specifc password instead of your mail pasword. Refer <a href=\'https://support.google.com/accounts/answer/185833\' target=\'_blank\'>https://support.google.com/accounts/answer/185833</a>\"}', '{\"from\":\"Name<username@gmail.com>\",\"username\":\"username@gmail.com\",\"mailbox\":{\"value\":\"{imap.gmail.com:993/imap/ssl}INBOX\",\"disabled\":true,\"checked\":true},\"smtp\":{\"value\":\"NA\",\"disabled\":true}}'),
('mail_sender', 'Mailchimp Mandrill', '{\"dsn_type\":\"mailchimp_mandrill\",\"disp_note\":\"\"}', '{\"from\":\"\",\"username\":\"\",\"mailbox\":{\"value\":\"\",\"disabled\":false,\"checked\":false},\"smtp\":{\"value\":\"NA\",\"disabled\":true}}'),
('mail_sender', 'Mailgun', '{\"dsn_type\":\"mailgun\",\"disp_note\":\"\"}', '{\"from\":\"\",\"username\":\"\",\"mailbox\":{\"value\":\"\",\"disabled\":false,\"checked\":false},\"smtp\":{\"value\":\"NA\",\"disabled\":true}}'),
('mail_sender', 'Mailjet', '{\"dsn_type\":\"mailjet\",\"disp_note\":\"Note: Provide the value for ACCESS_KEY at \'SMTP Username\' field and SECRET_KEY at \'SMTP Password\' field.\"}', '{\"from\":\"\",\"username\":\"\",\"mailbox\":{\"value\":\"\",\"disabled\":false,\"checked\":false},\"smtp\":{\"value\":\"NA\",\"disabled\":true}}'),
('mail_sender', 'Microsoft (outlook.com/live.com)', '{\"dsn_type\":\"microsoft\",\"disp_note\":\"Note: Refer <a href=\'https://support.microsoft.com/en-us/office/pop-imap-and-smtp-settings-for-outlook-com-d088b986-291d-42b8-9564-9c414e2aa040\' target=\'_blank\'>https://support.microsoft.com/en-us/office/pop-imap-and-smtp-settings-for-outlook-com-d088b986-291d-42b8-9564-9c414e2aa040</a>\"}', '{\"from\":\"Name<username@outlook.com>\",\"username\":\"username@outlook.com\",\"mailbox\":{\"value\":\"{outlook.office365.com:993/imap/ssl/novalidate-cert}INBOX\",\"disabled\":true,\"checked\":true},\"smtp\":{\"value\":\"smtp.office365.com:587\",\"disabled\":false}}'),
('mail_template', 'My Bank', '{\"disp_note\":\"Desc: A sample HTML rich phishing mail\"}', '{\"mail_template_subject\":\"Important! Your consent is required\",\"mail_template_content\":\"<br><div><table align=\\\"center\\\" border=\\\"0\\\" cellpadding=\\\"0\\\" cellspacing=\\\"0\\\" width=\\\"100%\\\"><tbody><tr><td><br><\\/td><\\/tr><\\/tbody><\\/table><\\/div><table align=\\\"center\\\" border=\\\"0\\\" cellpadding=\\\"0\\\" cellspacing=\\\"0\\\" width=\\\"600\\\"><tbody><tr><td bgcolor=\\\"#dcddde\\\" style=\\\"line-height:0px;background-color:#dcddde; border-left:1px solid #dcddde;\\\" valign=\\\"top\\\"><div><a data-original-title=\\\"Mark as smart link\\\" href=\\\"https:\\/\\/myphishingsite.com\\/page?rid={{RID}}\\\" rel=\\\"tooltip\\\" target=\\\"_blank\\\"><img src=\\\"https:\\/\\/user-images.githubusercontent.com\\/15928266\\/105949193-4518f300-60a7-11eb-87a9-6bb241003d92.jpg\\\" alt=\\\"\\\" class=\\\"fr-fic fr-dii\\\" width=\\\"100%\\\" border=\\\"0\\\"><\\/a><\\/div><\\/td><\\/tr><tr><td style=\\\"border-bottom:1px solid #cccccc;border-left:1px solid #cccccc;border-right:1px solid #cccccc;\\\"><table border=\\\"0\\\" cellpadding=\\\"0\\\" cellspacing=\\\"0\\\" width=\\\"100%\\\"><tbody><tr><td align=\\\"center\\\" valign=\\\"top\\\"><table align=\\\"center\\\" border=\\\"0\\\" cellpadding=\\\"0\\\" cellspacing=\\\"0\\\" width=\\\"100%\\\"><tbody><tr><td width=\\\"4%\\\"><br><\\/td><td valign=\\\"top\\\" width=\\\"92%\\\"><table border=\\\"0\\\" cellpadding=\\\"0\\\" cellspacing=\\\"0\\\" style=\\\"width:100%!important;\\\" width=\\\"100%\\\"><tbody><tr><td align=\\\"center\\\" valign=\\\"top\\\"><div><table align=\\\"center\\\" border=\\\"0\\\" cellpadding=\\\"0\\\" cellspacing=\\\"0\\\" style=\\\"width:100% !important;\\\" width=\\\"100%\\\"><tbody><tr><td height=\\\"20\\\"><br><\\/td><\\/tr><tr><td style=\\\"font-family:Arial; font-size:1em; line-height:22px; color:#595959;\\\">Dear {{NAME}},<\\/td><\\/tr><tr><td height=\\\"10\\\"><br><\\/td><\\/tr><tr><td style=\\\"font-family:Arial; font-size:1em; line-height:22px; color:#595959;\\\">We value our association with you and look forward to enhancing this relationship at every step.<\\/td><\\/tr><tr><td height=\\\"10\\\"><br><\\/td><\\/tr><tr><td style=\\\"font-family:Arial; font-size:1em; line-height:22px; color:#595959;\\\">We are delighted to inform you that you are a part of Platinum Banking Programme and to continue enjoying programme benefits, kindly provide your consent.<\\/td><\\/tr><tr><td height=\\\"10\\\"><br><\\/td><\\/tr><tr><td style=\\\"font-family:Arial; font-size:1em; line-height:22px; color:#595959;\\\">Here are few privileges of the programme, exclusively for you.<\\/td><\\/tr><tr><td style=\\\"text-align:center;\\\" valign=\\\"top\\\"><div align=\\\"center\\\" style=\\\"width:180px; display:inline-block; vertical-align:top;\\\"><table align=\\\"center\\\" border=\\\"0\\\" cellpadding=\\\"0\\\" cellspacing=\\\"0\\\" style=\\\"border-collapse:collapse!important;width:100%!important;\\\" width=\\\"100%\\\"><tbody><tr><td align=\\\"center\\\"><table align=\\\"center\\\" border=\\\"0\\\" cellpadding=\\\"0\\\" cellspacing=\\\"0\\\" width=\\\"100%\\\"><tbody><tr><td height=\\\"5\\\"><br><\\/td><\\/tr><tr><td align=\\\"center\\\" height=\\\"87\\\" style=\\\"vertical-align:middle !important;\\\" valign=\\\"middle\\\"><img src=\\\"https:\\/\\/user-images.githubusercontent.com\\/15928266\\/105949203-46e2b680-60a7-11eb-9a7f-c7a078cc4ca6.jpg\\\" alt=\\\"\\\" class=\\\"fr-fic fr-dii\\\" width=\\\"48\\\" height=\\\"48\\\" border=\\\"0\\\"><\\/td><\\/tr><tr><td align=\\\"center\\\" height=\\\"75\\\" style=\\\"font-family:Arial, Helvetica, sans-serif; line-height:22px; font-size:0.938em; color:#595959; text-align:center;\\\" valign=\\\"top\\\">Personalized attention from a dedicated Platinum Relationship Manager<\\/td><\\/tr><\\/tbody><\\/table><\\/td><\\/tr><\\/tbody><\\/table><\\/div><div align=\\\"center\\\" style=\\\"width:180px; display:inline-block; vertical-align:top;\\\"><table align=\\\"center\\\" border=\\\"0\\\" cellpadding=\\\"0\\\" cellspacing=\\\"0\\\" style=\\\"border-collapse:collapse!important;width:100%!important;\\\" width=\\\"100%\\\"><tbody><tr><td align=\\\"center\\\"><table align=\\\"center\\\" border=\\\"0\\\" cellpadding=\\\"0\\\" cellspacing=\\\"0\\\" width=\\\"100%\\\"><tbody><tr><td height=\\\"5\\\"><br><\\/td><\\/tr><tr><td align=\\\"center\\\" height=\\\"87\\\" style=\\\"vertical-align:middle !important;\\\" valign=\\\"middle\\\"><img src=\\\"https:\\/\\/user-images.githubusercontent.com\\/15928266\\/105949204-46e2b680-60a7-11eb-8b0a-b175a65b5018.jpg\\\" alt=\\\"\\\" class=\\\"fr-fic fr-dii\\\" width=\\\"56\\\" height=\\\"52\\\" border=\\\"0\\\"><\\/td><\\/tr><tr><td align=\\\"center\\\" height=\\\"110\\\" style=\\\"font-family:Arial, Helvetica, sans-serif; line-height:22px; font-size:0.938em; color:#595959; text-align:center;\\\" valign=\\\"top\\\">ZERO cost on locker<br>rental<\\/td><\\/tr><\\/tbody><\\/table><\\/td><\\/tr><\\/tbody><\\/table><\\/div><div align=\\\"center\\\" style=\\\"width:180px; display:inline-block; vertical-align:top;\\\"><table align=\\\"center\\\" border=\\\"0\\\" cellpadding=\\\"0\\\" cellspacing=\\\"0\\\" style=\\\"border-collapse:collapse!important;width:100%!important;\\\" width=\\\"100%\\\"><tbody><tr><td align=\\\"center\\\"><table align=\\\"center\\\" border=\\\"0\\\" cellpadding=\\\"0\\\" cellspacing=\\\"0\\\" width=\\\"100%\\\"><tbody><tr><td height=\\\"5\\\"><br><\\/td><\\/tr><tr><td align=\\\"center\\\" height=\\\"87\\\" style=\\\"vertical-align:middle !important;\\\" valign=\\\"middle\\\"><img src=\\\"https:\\/\\/user-images.githubusercontent.com\\/15928266\\/105949205-477b4d00-60a7-11eb-9d32-41427f2c1601.jpg\\\" alt=\\\"\\\" class=\\\"fr-fic fr-dii\\\" width=\\\"53\\\" height=\\\"45\\\" border=\\\"0\\\"><\\/td><\\/tr><tr><td align=\\\"center\\\" height=\\\"110\\\" style=\\\"font-family:Arial, Helvetica, sans-serif; line-height:22px; font-size:0.938em; color:#595959; text-align:center;\\\" valign=\\\"top\\\">Special relationship rates for Loans and Forex transactions<\\/td><\\/tr><\\/tbody><\\/table><\\/td><\\/tr><\\/tbody><\\/table><\\/div><\\/td><\\/tr><tr><td align=\\\"center\\\" valign=\\\"top\\\"><table align=\\\"center\\\" bgcolor=\\\"#0d4c8b\\\" border=\\\"0\\\" cellpadding=\\\"0\\\" cellspacing=\\\"0\\\" style=\\\"width:230px !important; border:1px solid #733943; border-radius:5px; background-color:#733943; font-size: 15px;\\\" width=\\\"230\\\"><tbody><tr><td align=\\\"center\\\" style=\\\"font-family:Arial, sans-serif; font-size:1.2em; color:#fff; text-align:center !important; border-radius:5px; background-color:#733943; padding:5px;\\\" valign=\\\"middle\\\"><a data-original-title=\\\"Mark as smart link\\\" href=\\\"https:\\/\\/myphishingsite.com\\/page?rid={{RID}}\\\" rel=\\\"tooltip\\\" style=\\\"text-decoration:none; color:#fff; font-weight:500;\\\" target=\\\"_blank\\\">Platinum Banking Benefits<\\/a><\\/td><\\/tr><\\/tbody><\\/table><\\/td><\\/tr><tr><td height=\\\"20\\\"><br><\\/td><\\/tr><tr><td align=\\\"center\\\" valign=\\\"top\\\"><table align=\\\"center\\\" bgcolor=\\\"#0d4c8b\\\" border=\\\"0\\\" cellpadding=\\\"0\\\" cellspacing=\\\"0\\\" style=\\\"width:200px !important; border:1px solid #733943; border-radius:5px; background-color:#733943; font-size: 15px;\\\" width=\\\"200\\\"><tbody><tr><td align=\\\"center\\\" style=\\\"font-family:Arial, sans-serif; font-size:1.2em; color:#fff; text-align:center !important; border-radius:5px; background-color:#733943; padding:5px;\\\" valign=\\\"middle\\\"><a data-original-title=\\\"Mark as smart link\\\" href=\\\"https:\\/\\/myphishingsite.com\\/page?rid={{RID}}\\\" rel=\\\"tooltip\\\" style=\\\"text-decoration:none; color:#fff; font-weight:500;\\\" target=\\\"_blank\\\">Yes, I want to Continue<\\/a><\\/td><\\/tr><\\/tbody><\\/table><\\/td><\\/tr><tr><td height=\\\"15\\\"><br><\\/td><\\/tr><tr><td height=\\\"20\\\"><br><\\/td><\\/tr><\\/tbody><\\/table><\\/div><\\/td><\\/tr><tr><td height=\\\"30\\\"><br><\\/td><\\/tr><tr><td align=\\\"left\\\" style=\\\"font-family:Arial; font-size:16px; letter-spacing: 1px; line-height:28px; color:#000000;\\\" valign=\\\"top\\\">Warm regards,<br><br><div><span style=\\\"font-weight: bold !important;\\\">Aaron Murakami<\\/span><br>Programme Manager<br>Platinum Premium Banking<\\/div><\\/td><\\/tr><tr><td height=\\\"15\\\"><br><\\/td><\\/tr><\\/tbody><\\/table><\\/td><td width=\\\"4%\\\"><br><\\/td><\\/tr><\\/tbody><\\/table><\\/td><\\/tr><\\/tbody><\\/table><\\/td><\\/tr><tr><td align=\\\"left\\\" style=\\\"font-family:Arial, Helvetica, sans-serif; font-size:11px; line-height:16px; padding:10px 5px 5px 18px; color:#201d1e; text-align:left;\\\" valign=\\\"top\\\">*Terms &amp; Conditions apply | <a data-original-title=\\\"Mark as smart link\\\" href=\\\"https:\\/\\/myphishingsite.com\\/unsubscribe\\\" rel=\\\"tooltip\\\" style=\\\"text-decoration:underline; color:#0000ff;\\\" target=\\\"_blank\\\">Unsubscribe<\\/a><\\/td><\\/tr><tr><td style=\\\"font-family:Arial, Helvetica, sans-serif; font-size:12px; line-height:14px; padding:10px 0 5px 18px; color:#000000;\\\">*Based on Retail Loan book size (excluding mortgages). Source: Annual Reports as on 31<sup>st<\\/sup> March 2018 and No.1 on market capitalisation based on BSE data as on 22<sup>nd<\\/sup> May, 2018.<\\/td><\\/tr><\\/tbody><\\/table><div><br><\\/div><br>{{TRACKER}}\",\"timage_type\":1,\"mail_content_type\":\"text/html\",\"attachment\":[]}'),
('mail_sender', 'MailPace', '{\"dsn_type\":\"mailpace\",\"disp_note\":\"Note: Provide the value of API_TOKEN at \'SMTP Password\' field.\"}', '{\"from\":\"\",\"username\":\"\",\"mailbox\":{\"value\":\"\",\"disabled\":false,\"checked\":false},\"smtp\":{\"value\":\"NA\",\"disabled\":true}}'),
('mail_sender', 'Postmark', '{\"dsn_type\":\"postmark\",\"disp_note\":\"Note: Provide the value of ID at \'SMTP Password\' field.\"}', '{\"from\":\"\",\"username\":\"\",\"mailbox\":{\"value\":\"\",\"disabled\":false,\"checked\":false},\"smtp\":{\"value\":\"NA\",\"disabled\":true}}'),
('mail_template', 'Scan me - QR', '{\"disp_note\":\"Desc: A QR based email. QR code is generated dynamicly\"}', '{\"mail_template_subject\":\"Lucky You\",\"mail_template_content\":\"Dear Customer,<br><br>Please scan the QR image shown below to confirm your prize!<br><br><img src=\\\"http:\\/\\/localhost\\/mod?type=qr_att&amp;content=<your text here>&amp;img_name=code.png\\\" class=\\\"fr-fic fr-dii\\\"><br><br>{{TRACKER}}\",\"timage_type\":1,\"mail_content_type\":\"text/html\",\"attachment\":[]}'),
('mail_sender', 'Sendgrid', '{\"dsn_type\":\"sendgrid\",\"disp_note\":\"Note: Provide value of KEY at \'SMTP Password\' field.\"}', '{\"from\":\"\",\"username\":\"\",\"mailbox\":{\"value\":\"\",\"disabled\":false,\"checked\":false},\"smtp\":{\"value\":\"NA\",\"disabled\":true}}'),
('mail_sender', 'Sendinblue', '{\"dsn_type\":\"sendinblue\",\"disp_note\":\"\"}', '{\"from\":\"\",\"username\":\"\",\"mailbox\":{\"value\":\"\",\"disabled\":false,\"checked\":false},\"smtp\":{\"value\":\"NA\",\"disabled\":true}}'),
('mail_template', 'Track me', '{\"disp_note\":\"Desc: A simple mail to track when the mail is opened\"}', '{\"mail_template_subject\":\"Thanks!\",\"mail_template_content\":\"Hi {{FNAME}},<br><br>Thank you for your email. We will meet soon.<br><br>Thanks &amp; Regards<br>Rose<br><br>{{TRACKER}}\",\"timage_type\":1,\"mail_content_type\":\"text/html\",\"attachment\":[]}'),
('mail_sender', 'Yahoo (yahoo.com/ymail.com) - SSL', '{\"dsn_type\":\"yahoo_ssl\",\"disp_note\":\"Note: You may need to turn on less secure apps. Refer <a href=\'https://help.yahoo.com/kb/access-yahoo-mail-third-party-apps-sln15241.html\' target=\'_blank\'>https://help.yahoo.com/kb/access-yahoo-mail-third-party-apps-sln15241.html</a>\"}', '{\"from\":\"Name<username@yahoo.com>\",\"username\":\"username@yahoo.com\",\"mailbox\":{\"value\":\"{imap.mail.yahoo.com:993/imap/ssl}INBOX\",\"disabled\":true,\"checked\":true},\"smtp\":{\"value\":\"smtp.mail.yahoo.com:465\",\"disabled\":false}}'),
('mail_sender', 'Yahoo (yahoo.com/ymail.com) - TLS', '{\"dsn_type\":\"yahoo_tls\",\"disp_note\":\"Note: You may need to turn on less secure apps. Refer <a href=\'https://help.yahoo.com/kb/access-yahoo-mail-third-party-apps-sln15241.html\' target=\'_blank\'>https://help.yahoo.com/kb/access-yahoo-mail-third-party-apps-sln15241.html</a>\"}', '{\"from\":\"Name<username@yahoo.com>\",\"username\":\"username@yahoo.com\",\"mailbox\":{\"value\":\"{imap.mail.yahoo.com:993/imap/ssl}INBOX\",\"disabled\":true,\"checked\":true},\"smtp\":{\"value\":\"smtp.mail.yahoo.com:587\",\"disabled\":false}}');

-- --------------------------------------------------------
--
-- Dumping data for table `tb_core_mailcamp_config`
--
INSERT INTO `tb_core_mailcamp_config` (`mconfig_id`, `mconfig_name`, `mconfig_data`, `date`) VALUES
('default', 'Default Configuration', '{\"mail_sign\":{\"cert\":[],\"pvk\":[]},\"mail_enc\":{\"cert\":[]},\"peer_verification\":true,\"recipient_type\":\"to\",\"signed_mail\":false,\"encrypted_mail\":false,\"antiflood\":{\"limit\":\"50\",\"pause\":\"30\"},\"msg_priority\":\"3\"}', NULL);

EOD;


    if ($conn->multi_query($tables)) 
        while ($conn->more_results() && $conn->next_result());
    
    if(mysqli_error($conn)) 
        return false;
    else
        return true;
}
?>
