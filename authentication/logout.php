<?php
session_start();
$_SESSION = [];
session_destroy();
header('Location: /smart_rent/authentication/login.php');
?>
exit;