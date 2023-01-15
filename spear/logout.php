<?php 
require_once(dirname(__FILE__) . '/config/db.php');
require_once(dirname(__FILE__) . '/manager/session_manager.php');
$entry_time = (new DateTime())->format('d-m-Y h:i A');

// remove all session variables
@ob_start();
session_start();
updateLoginLogout($conn, $_SESSION['username'], $entry_time, false);

//Keep last 1000 entries and delete remaining
$stmt = $conn->prepare("DELETE FROM tb_log WHERE id NOT IN (SELECT id FROM (SELECT id FROM tb_log ORDER BY id DESC LIMIT 1000) x)");
$stmt->execute();

session_destroy();
header("Location: ../spear");

//---------------------------------------------------------------
?>
