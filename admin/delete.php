<?php
session_start();
require_once '../middleware.php';
runMiddleware();
require_once '../config/db_connection.php';

// Pārbaudām, vai lietotājs ir administrators
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit;
}

// Pārbaudām, vai ir norādīts rezervācijas ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['message'] = "Nepareizs rezervācijas ID.";
    $_SESSION['message_type'] = "error";
    header("Location: index.php");
    exit;
}

$booking_id = (int)$_GET['id'];

try {
    // Pārbaudām, vai rezervācija ar šādu ID eksistē
    $stmt = $pdo->prepare("SELECT id FROM bookings WHERE id = ?");
    $stmt->execute([$booking_id]);
    $booking = $stmt->fetch();

    if (!$booking) {
        $_SESSION['message'] = "Rezervācija ar ID $booking_id netika atrasta.";
        $_SESSION['message_type'] = "error";
        header("Location: index.php");
        exit;
    }

    // Dzēšam rezervāciju
    $stmt = $pdo->prepare("DELETE FROM bookings WHERE id = ?");
    $stmt->execute([$booking_id]);

    // Iestatām veiksmes ziņojumu
    $_SESSION['message'] = "Rezervācija ar ID $booking_id tika veiksmīgi dzēsta.";
    $_SESSION['message_type'] = "success";

} catch (PDOException $e) {
    // Iestatām kļūdas ziņojumu
    $_SESSION['message'] = "Kļūda dzēšot rezervāciju: " . $e->getMessage();
    $_SESSION['message_type'] = "error";
}

// Novirzām atpakaļ uz rezervāciju sarakstu
header("Location: index.php");
exit;
?>