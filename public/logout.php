<?php
require_once '../middleware.php';
runMiddleware();

// Izdzēšam visu sesiju
session_unset();
session_destroy();
$_SESSION = []; // Drošības labad iztukšojam masīvu
header("Location: login.php");
exit;
?>