<?php
session_start();
session_unset();
session_destroy();
header("Location: adminlogin.php"); // Or login.html
exit();
?>