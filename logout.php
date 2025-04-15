<?php
require_once 'middleware.php';
runMiddleware();

// Saglabājam, vai lietotājs ir admin, pirms sesijas iznīcināšanas
$isAdmin = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;

// Iznīcinām sesiju
session_unset();
session_destroy();
$_SESSION = [];

// Novirzām uz attiecīgo ielogošanās lapu
if ($isAdmin) {
    header("Location: admin/login.php");
} else {
    header("Location: public/login.php");
}
exit;
?>