<?php
function exception_error_handler($errno, $errstr, $errfile, $errline ) {
    throw new ErrorException($errstr, $errno, 0, $errfile, $errline);
}
set_error_handler("exception_error_handler");

//------------------------------------------------------
function checkInstallation(){
  $db_file = dirname(__FILE__) . '/db.php';

  if(file_exists($db_file)){
    require_once(dirname(__FILE__) . '/db.php');

  $result = mysqli_query($conn, "SHOW TABLES FROM $curr_db");
  if(mysqli_num_rows($result) > 0)
    die("Already installed!");  
  else
      return false;
  }
}

//------------------------------------------------------
function getOSType($conn){
	if(stripos(PHP_OS, 'WIN') === 0)
		return "windows";
	else
		return "linux";
}

function getPHPBinaryLocation($os){
	if($os == "windows")
		return dirname(php_ini_loaded_file()).DIRECTORY_SEPARATOR.'php.exe';
	else
		return PHP_BINDIR.DIRECTORY_SEPARATOR.'php';
}

function isProcessRunning($conn,$os){	//Single instance manager (check if 'our' php cron running)
	$stmt = $conn->prepare("SELECT pid FROM tb_main_cron");
	$stmt->execute();
	$result = $stmt->get_result();
	$row = $result->fetch_assoc();
	$prev_pid = $row['pid'];

	if($os == "windows"){
		$task_list = shell_exec("tasklist | findstr php.exe");
		$task_list_arr = explode("\n", $task_list);

		foreach ($task_list_arr as $process) {
		    $process_info = array_values(array_filter(explode(" ", $process)));
		    try {
		    	if($process_info[1] == $prev_pid){	//Exit if cron running
		    		return true;
		    	}
		    }
		    catch(Exception $e) {}
		}
	}
	else{
		$handle = popen("ps aux | grep php | grep -v grep | awk '{ print $2 }'","r");
		$process_ids = explode("\n",fread($handle, 2096));
		foreach ($process_ids as $process) {
			if($process == $prev_pid)	//Exit if cron running
		    		return true;
		}
		pclose($handle);
	}
	return false;
}

function startProcess($os){
	if($os == "windows"){
		pclose(popen("start /b ".getPHPBinaryLocation($os)." SniperPhish_Manager.php quite","r"));	//background execution
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

?>