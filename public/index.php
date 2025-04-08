<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Kopīgas funkcijas
require_once '../middleware.php';
runMiddleware();

// Pieprasījums: pievienojam jaunu rezervāciju

require_once '../config/db_connection.php';

// Izvēlētais datums no GET vai noklusējuma šodiena
$selectedDate = isset($_GET['selected_date']) ? $_GET['selected_date'] : date('Y-m-d');

// Kalendāra aprēķini
$month = isset($_GET['month']) ? sprintf("%02d", (int)$_GET['month']) : date('m');
$year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');
$timestamp = mktime(0, 0, 0, $month, 1, $year);
$daysInMonth = date('t', $timestamp);
$firstDayOfWeek = date('w', $timestamp);
$monthName = date('F', $timestamp);

$prevMonth = $month - 1;
$prevYear = $year;
if ($prevMonth < 1) {
    $prevMonth = 12;
    $prevYear--;
}
$nextMonth = $month + 1;
$nextYear = $year;
if ($nextMonth > 12) {
    $nextMonth = 1;
    $nextYear++;
}

// Laika slotu ielāde
$timeslotsQuery = "
    SELECT t.day_part, t.time_slot 
    FROM timeslots t
    LEFT JOIN bookings b ON t.time_slot = b.time_slot AND b.booking_date = ?
    WHERE b.time_slot IS NULL
    ORDER BY FIELD(t.day_part, 'morning', 'day', 'evening'), STR_TO_DATE(t.time_slot, '%H:%i')";
$stmt = $conn->prepare($timeslotsQuery);
$stmt->bind_param("s", $selectedDate);
$stmt->execute();
$resultTimeslots = $stmt->get_result();
$timeslots = [];
while ($row = $resultTimeslots->fetch_assoc()) {
    $timeslots[$row['day_part']][] = $row['time_slot'];
}
$resultTimeslots->free();
$stmt->close();

// Pakalpojumu ielāde
$showServices = false;
$selectedSlot = null;
if (isset($_GET['action']) && $_GET['action'] === 'show_services' && isset($_GET['slot'])) {
    $showServices = true;
    $selectedSlot = $_GET['slot'];

    $servicesQuery = "SELECT * FROM services";
    $servicesResult = $conn->query($servicesQuery);
    if (!$servicesResult) {
        die("Kļūda pakalpojumu vaicājumā: " . $conn->error);
    }
    $services = [];
    while ($srv = $servicesResult->fetch_assoc()) {
        $services[] = $srv;
    }
    $servicesResult->free();
}

// Rezervācijas apstrāde
$bookingMessage = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'book_service') {
    $serviceId = (int)$_POST['service_id'];
    $selectedSlot = $_POST['slot'];
    $userName = $_POST['user_name'];
    $phone = $_POST['phone'];
    $bookingDate = $_POST['booking_date'];

    $checkStmt = $conn->prepare("SELECT COUNT(*) FROM bookings WHERE time_slot = ? AND booking_date = ?");
    $checkStmt->bind_param("ss", $selectedSlot, $bookingDate);
    $checkStmt->execute();
    $checkStmt->bind_result($count);
    $checkStmt->fetch();
    $checkStmt->close();

    if ($count > 0) {
        $bookingMessage = "Šis laika slots jau ir rezervēts!";
    } else {
        $stmt = $conn->prepare("INSERT INTO bookings (service_id, time_slot, booking_date, user_name, phone) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issss", $serviceId, $selectedSlot, $bookingDate, $userName, $phone);
        if ($stmt->execute()) {
            $bookingMessage = "Rezervācija veiksmīga!";
        } else {
            $bookingMessage = "Rezervācijas saglabāšana neizdevās: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Rezervācijas atcelšana
if (isset($_GET['action']) && $_GET['action'] === 'cancel_booking' && isset($_GET['booking_id'])) {
    $bookingId = (int)$_GET['booking_id'];
    $stmt = $conn->prepare("DELETE FROM bookings WHERE id = ?");
    $stmt->bind_param("i", $bookingId);
    if ($stmt->execute()) {
        $cancelMessage = "Jūsu rezervācija ir veiksmīgi atcelta.";
    } else {
        $cancelMessage = "Kļūda atceļot rezervāciju: " . $stmt->error;
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <title>MR ONLAINS Calendar</title>
    <link rel="stylesheet" href="../css/base.css">
    <link rel="stylesheet" href="../css/calendar.css">
    <link rel="stylesheet" href="../css/timeslots.css">
    <link rel="stylesheet" href="../css/procedures.css">
    <link rel="stylesheet" href="../css/bookings.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <?php if ($bookingMessage): ?>
        <div class="message">
            <?php echo htmlspecialchars($bookingMessage); ?>
        </div>
    <?php endif; ?>

    <?php include '../includes/calendar.php'; ?>
    <?php include '../includes/timeslots.php'; ?>
    <?php include '../includes/procedures.php'; ?>
    <?php include '../includes/bookings.php'; ?>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>