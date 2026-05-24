<?php
require_once '../config/db.php';
$_SESSION = [];
session_destroy();
header("Location: ../auth/login.php");
exit;
?>