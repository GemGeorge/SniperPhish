<?php 
require_once(dirname(__FILE__) . '/config/db.php');
require_once(dirname(__FILE__) . '/manager/common_functions.php');

// remove all session variables
@ob_start();
session_start();
logIt("Account logout");
session_destroy();
header("Location: ../spear");

//---------------------------------------------------------------
?>
