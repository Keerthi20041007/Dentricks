<?php
session_start();
session_unset();
session_destroy();
header("Location: code.php"); // Or login.html
exit();
?>