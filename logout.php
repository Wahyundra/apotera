<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

session_destroy();
header("Location: index.php");
exit();
?>