<?php 
require_once(dirname(__FILE__) . '/config/db.php');
require_once(dirname(__FILE__) . '/manager/session_manager.php');
$entry_time = (new DateTime())->format('d-m-Y h:i A');

// remove all session variables
@ob_start();
session_start();
updateLoginLogout($conn, $_SESSION['username'], $entry_time, false);
session_destroy();
header("Location: ../spear");

//---------------------------------------------------------------
?>
