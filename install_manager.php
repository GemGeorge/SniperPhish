<?php
error_reporting(E_ALL);
ini_set('display_errors', true);
require_once (dirname(__FILE__) . '/spear/common_functions.php');

if (isset($_POST['action_type']))
{
    if ($_POST['action_type'] == "check_requirements") checkRequirements();
    if ($_POST['action_type'] == "do_install") doInstall();
}
else die();

//----------------------------------------------------------------------
function checkRequirements()
{
    $resp_arr = [];
    $f_error = false;
    $extensions = get_loaded_extensions();
    $success = "<i class='fas fa-check fa-lg text-success'></i>";

    if (phpversion() >= 7.3) $resp_arr['PHP version ' . phpversion() ] = $success;
    else
    {
        $resp_arr['PHP version ' . phpversion() ] = "<i class='fas fa-times fa-lg text-danger' data-toggle='tooltip' title='PHP version >= 7.3 is required'></i>";
        $f_error = true;
    }

    if (in_array("imap", $extensions)) $resp_arr['PHP extension imap'] = $success;
    else
    {
        $resp_arr['PHP extension imap'] = "<i class='fas fa-times fa-lg text-danger' data-toggle='tooltip' title='Imap extension should be loaded'></i>";
        $f_error = true;
    }

    if (in_array("gd", $extensions)) $resp_arr['PHP extension gd'] = $success;
    else
    {
        $resp_arr['PHP extension gd'] = "<i class='fas fa-times fa-lg text-danger' data-toggle='tooltip' title='gd extension should be loaded'></i>";
        $f_error = true;
    }

    if (is_callable('shell_exec') && false === stripos(ini_get('disable_functions') , 'shell_exec')) $resp_arr['PHP function shell_exec'] = $success;
    else
    {
        $resp_arr['PHP function shell_exec'] = "<i class='fas fa-times fa-lg text-danger' data-toggle='tooltip' title='PHP shell_exec should be enabled'></i>";
        $f_error = true;
    }

    $resp_arr['code'] = $f_error;
    header('Content-Type: application/json');
    echo (json_encode($resp_arr));
}

function doInstall()
{
    checkInstallation(); //check installation
    $db_name = $_POST['db_name'];
    $db_host = $_POST['db_host'];
    $db_user_name = $_POST['db_user_name'];
    $db_user_pwd = $_POST['db_user_pwd'];
    $user_contact_mail = $_POST['user_contact_mail'];
    $timezone_format = $_POST['timezone_format'];

    $conn = mysqli_connect($db_host, $db_user_name, $db_user_pwd, $db_name);

    if (mysqli_connect_errno())
    {
        echo ("Connection failed: " . mysqli_connect_error());
    }
    else
    {
        $file_contents = '<?php
$curr_db = "'.$db_name.'";
$conn = mysqli_connect("'.$db_host.'","'.$db_user_name.'","'.$db_user_pwd.'",$curr_db);

if (mysqli_connect_errno()) {
    die("DB connection failed!");
} 
?>';
        if(file_put_contents('spear/db.php', $file_contents)){ //created db.php file
            if (createTables($conn)) //creates SniperPhish DB tables
                if (modifySniperPhishSettings($conn, $timezone_format, $user_contact_mail))
                    echo ("success");
                else 
                    echo ("Error in timezone update");
            else
                echo ("Error in creating database tables! Retry or empty the database.");
        }
        else
            echo ("Error writing file spear/db.php");
    }

}

function modifySniperPhishSettings($conn, $timezone_format, $user_contact_mail)
{   
    $stmt = $conn->prepare("UPDATE tb_main_variables SET report_time_zone=?, sniperphish_time_zone=?");
    $stmt->bind_param('ss', $timezone_format, $timezone_format);

    if ($stmt->execute() === true) {
        $stmt = $conn->prepare("UPDATE tb_main SET contact_mail=?");
        $stmt->bind_param('s', $user_contact_mail);

        if ($stmt->execute() === true) 
            return true;
        else
            return false;
    }
    else
        return false;
}

function createTables($conn)
{

    $tables = <<<'EOD'
-- phpMyAdmin SQL Dump
-- version 5.0.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 17, 2020 at 04:15 PM
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
-- Database: `dbslxbx_fish`
--

-- --------------------------------------------------------

--
-- Table structure for table `tb_core_mailcamp_list`
--

CREATE TABLE `tb_core_mailcamp_list` (
  `campaign_id` varchar(50) NOT NULL,
  `campaign_name` varchar(50) NOT NULL,
  `user_group` varchar(111) NOT NULL,
  `mail_template` varchar(111) NOT NULL,
  `mail_sender` varchar(111) NOT NULL,
  `date` varchar(111) NOT NULL,
  `scheduled_time` varchar(111) NOT NULL,
  `stop_time` varchar(111) DEFAULT NULL,
  `msg_interval` varchar(111) NOT NULL DEFAULT '0000-0000',
  `msg_fail_retry` int(11) NOT NULL DEFAULT 0,
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
  `sender_SMTP_server` varchar(50) NOT NULL,
  `sender_from` varchar(111) NOT NULL,
  `sender_acc_username` varchar(111) NOT NULL,
  `sender_acc_pwd` varchar(50) NOT NULL,
  `sender_mailbox` varchar(1111) DEFAULT NULL,
  `cust_headers` varchar(1111) DEFAULT NULL,
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
  `cust_timage` tinyint(4) NOT NULL DEFAULT 0,
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
  `user_name` mediumtext DEFAULT NULL,
  `user_email` mediumtext DEFAULT NULL,
  `user_notes` mediumtext DEFAULT NULL,
  `date` varchar(111) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tb_core_simple_tracker_list`
--

CREATE TABLE `tb_core_simple_tracker_list` (
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
  `id` varchar(15) NOT NULL,
  `campaign_id` varchar(15) DEFAULT NULL,
  `campaign_name` varchar(50) DEFAULT NULL,
  `sending_status` tinyint(11) NOT NULL DEFAULT 0,
  `send_time` varchar(50) DEFAULT NULL,
  `mailto_user_name` varchar(50) DEFAULT NULL,
  `mailto_user_email` varchar(50) DEFAULT NULL,
  `send_error` varchar(111) DEFAULT NULL,
  `mail_open_times` mediumtext DEFAULT NULL,
  `public_ip` varchar(111) DEFAULT NULL,
  `user_agent` varchar(1111) DEFAULT NULL,
  `mail_client` varchar(111) DEFAULT NULL,
  `platform` varchar(111) DEFAULT NULL,
  `all_headers` varchar(9999) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `tb_data_simple_tracker_live`
--

CREATE TABLE `tb_data_simple_tracker_live` (
  `id` int(111) NOT NULL,
  `tracker_id` varchar(111) DEFAULT NULL,
  `cid` varchar(111) DEFAULT NULL,
  `public_ip` varchar(111) DEFAULT NULL,
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
  `cid` varchar(222) DEFAULT NULL,
  `session_id` varchar(222) DEFAULT NULL,
  `public_ip` varchar(222) DEFAULT NULL,
  `internal_ip` varchar(222) DEFAULT NULL,
  `user_agent` varchar(222) DEFAULT NULL,
  `time` varchar(222) DEFAULT NULL,
  `browser` varchar(222) DEFAULT NULL,
  `platform` varchar(222) DEFAULT NULL,
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
  `cid` varchar(222) DEFAULT NULL,
  `session_id` varchar(222) DEFAULT NULL,
  `public_ip` varchar(222) DEFAULT NULL,
  `internal_ip` varchar(222) DEFAULT NULL,
  `user_agent` varchar(222) DEFAULT NULL,
  `time` varchar(222) DEFAULT NULL,
  `browser` varchar(222) DEFAULT NULL,
  `platform` varchar(222) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tb_main`
--

CREATE TABLE `tb_main` (
  `id` int(11) NOT NULL,
  `username` varchar(111) DEFAULT NULL,
  `password` varchar(222) DEFAULT NULL,
  `contact_mail` varchar(111) DEFAULT NULL,
  `v_hash` varchar(111) DEFAULT NULL,
  `v_hash_time` varchar(111) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tb_main`
--

INSERT INTO `tb_main` (`id`, `username`, `password`, `contact_mail`, `v_hash`, `v_hash_time`) VALUES
(1, 'admin', '23d119e1749d0d0f21dd751c52d3ca221462867669acaf58f209aa237a3955a3', 'gemgeorgex@gmail.com', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `tb_main_cron`
--

CREATE TABLE `tb_main_cron` (
  `id` int(11) NOT NULL,
  `pid` int(111) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `tb_main_cron`
--

INSERT INTO `tb_main_cron` (`id`, `pid`) VALUES
(1, 3492);

-- --------------------------------------------------------

--
-- Table structure for table `tb_main_variables`
--

CREATE TABLE `tb_main_variables` (
  `id` int(1) NOT NULL,
  `server_protocol` varchar(11) DEFAULT NULL,
  `domain` varchar(111) DEFAULT NULL,
  `baseurl` varchar(111) DEFAULT NULL,
  `report_time_zone` varchar(111) DEFAULT NULL,
  `report_time_format` varchar(111) DEFAULT NULL,
  `sniperphish_time_zone` varchar(111) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `tb_main_variables`
--

INSERT INTO `tb_main_variables` (`id`, `server_protocol`, `domain`, `baseurl`, `report_time_zone`, `report_time_format`, `sniperphish_time_zone`) VALUES
(1, 'http', 'localhost', 'http://localhost', 'Asia/Kuala_Lumpur,28800', 'DD-MM-YYYY,comaspace,hh:mm:ss A', 's');

--
-- Indexes for dumped tables
--

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
-- Indexes for table `tb_core_simple_tracker_list`
--
ALTER TABLE `tb_core_simple_tracker_list`
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
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tb_data_simple_tracker_live`
--
ALTER TABLE `tb_data_simple_tracker_live`
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
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tb_data_simple_tracker_live`
--
ALTER TABLE `tb_data_simple_tracker_live`
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
-- AUTO_INCREMENT for table `tb_main`
--
ALTER TABLE `tb_main`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tb_main_cron`
--
ALTER TABLE `tb_main_cron`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;




EOD;

    
    if ($conn->multi_query($tables)) 
        while ($conn->more_results() && $conn->next_result());
    
    if(mysqli_error($conn)) 
        return false;
    else
        return true;
}
?>
