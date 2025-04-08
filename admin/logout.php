<?php
require_once '../middleware.php';
runMiddleware();

// Izdzēšam sesiju un izlogojamies
session_unset();
session_destroy();
header("Location: login.php");
exit;
?>