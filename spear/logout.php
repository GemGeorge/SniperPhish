<?php
// remove all session variables
@ob_start();
session_start();
session_unset();

// destroy the session
session_destroy();

header("Location: ../spear");
?>
