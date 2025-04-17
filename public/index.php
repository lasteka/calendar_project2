<?php
require_once '../middleware.php';
runMiddleware();

require_once '../config/db_connection.php';

// Pārbaudām, vai lietotājs ir ielogojies
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

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

// Ielādējam visus laika slotus
$timeslotsQuery = "SELECT id, day_part, time_slot FROM timeslots ORDER BY FIELD(day_part, 'morning', 'day', 'evening'), STR_TO_DATE(time_slot, '%H:%i')";
$resultTimeslots = $conn->query($timeslotsQuery);
$allTimeslots = [];
while ($row = $resultTimeslots->fetch_assoc()) {
    $allTimeslots[$row['day_part']][] = $row;
}
$resultTimeslots->free();

// Iegūstam visas rezervācijas izvēlētajā datumā
$bookingsQuery = "
    SELECT b.time_slot, s.duration 
    FROM bookings b
    JOIN services s ON b.service_id = s.id
    WHERE b.booking_date = ?
";
$stmt = $conn->prepare($bookingsQuery);
$stmt->bind_param("s", $selectedDate);
$stmt->execute();
$bookingsResult = $stmt->get_result();
$bookedSlots = [];
while ($booking = $bookingsResult->fetch_assoc()) {
    $bookedSlots[] = [
        'time_slot' => $booking['time_slot'],
        'duration' => $booking['duration']
    ];
}
$bookingsResult->free();
$stmt->close();

// Filtrējam pieejamos laika slotus
$timeslots = [];
foreach ($allTimeslots as $dayPart => $slots) {
    $timeslots[$dayPart] = [];
    foreach ($slots as $slot) {
        $slotTime = strtotime($slot['time_slot']);
        $isAvailable = true;

        foreach ($bookedSlots as $booked) {
            $bookedStart = strtotime($booked['time_slot']);
            $bookedEnd = strtotime("+{$booked['duration']} minutes", $bookedStart);

            if ($slotTime >= $bookedStart && $slotTime < $bookedEnd) {
                $isAvailable = false;
                break;
            }
        }

        if ($isAvailable) {
            $timeslots[$dayPart][] = $slot['time_slot'];
        }
    }
}

// Pakalpojumu ielāde
$showServices = false;
$selectedSlot = null;
if (isset($_GET['action']) && $_GET['action'] === 'show_services' && isset($_GET['slot'])) {
    $showServices = true;
    $selectedSlot = $_GET['slot'];

    $servicesQuery = "SELECT id, name, price, duration FROM services";
    $servicesResult = $conn->query($servicesQuery);
    $services = [];
    while ($srv = $servicesResult->fetch_assoc()) {
        $services[] = $srv;
    }
    $servicesResult->free();
}

// Rezervācijas apstrāde
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'book_service') {
    $serviceId = (int)$_POST['service_id'];
    $selectedSlot = $_POST['slot'];
    $userName = $_POST['user_name'];
    $phone = $_POST['phone'];
    $bookingDate = $_POST['booking_date'];
    $userId = (int)$_SESSION['user_id'];

    $stmt = $conn->prepare("SELECT duration FROM services WHERE id = ?");
    $stmt->bind_param("i", $serviceId);
    $stmt->execute();
    $result = $stmt->get_result();
    $service = $result->fetch_assoc();
    $duration = $service['duration'];
    $stmt->close();

    $checkStmt = $conn->prepare("
        SELECT COUNT(*) 
        FROM bookings b
        JOIN services s ON b.service_id = s.id
        WHERE b.booking_date = ?
        AND (
            (STR_TO_DATE(b.time_slot, '%H:%i') >= STR_TO_DATE(?, '%H:%i') AND STR_TO_DATE(b.time_slot, '%H:%i') < DATE_ADD(STR_TO_DATE(?, '%H:%i'), INTERVAL ? MINUTE))
            OR
            (STR_TO_DATE(?, '%H:%i') >= STR_TO_DATE(b.time_slot, '%H:%i') AND STR_TO_DATE(?, '%H:%i') < DATE_ADD(STR_TO_DATE(b.time_slot, '%H:%i'), INTERVAL s.duration MINUTE))
        )
    ");
    $checkStmt->bind_param("sssiss", $bookingDate, $selectedSlot, $selectedSlot, $duration, $selectedSlot, $selectedSlot);
    $checkStmt->execute();
    $checkStmt->bind_result($count);
    $checkStmt->fetch();
    $checkStmt->close();

    if ($count > 0) {
        $_SESSION['booking_message'] = "Šis laika slots jau ir aizņemts!";
    } else {
        $stmt = $conn->prepare("INSERT INTO bookings (service_id, time_slot, booking_date, user_name, phone, user_id) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issssi", $serviceId, $selectedSlot, $bookingDate, $userName, $phone, $userId);
        if ($stmt->execute()) {
            $_SESSION['booking_message'] = "Rezervācija veiksmīga!";
        } else {
            $_SESSION['booking_message'] = "Rezervācijas kļūda: " . $stmt->error;
        }
        $stmt->close();
    }
    header("Location: index.php?selected_date=" . urlencode($selectedDate));
    exit;
}

// Rezervācijas atcelšana
if (isset($_GET['action']) && $_GET['action'] === 'cancel_booking' && isset($_GET['booking_id'])) {
    $bookingId = (int)$_GET['booking_id'];
    $userId = (int)$_SESSION['user_id'];

    $stmt = $conn->prepare("DELETE FROM bookings WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $bookingId, $userId);
    if ($stmt->execute()) {
        $_SESSION['booking_message'] = $stmt->affected_rows > 0 ? "Rezervācija atcelta!" : "Rezervācija netika atrasta!";
    } else {
        $_SESSION['booking_message'] = "Kļūda: " . $stmt->error;
    }
    $stmt->close();
    header("Location: index.php?selected_date=" . urlencode($selectedDate));
    exit;
}
?>
<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <title>MR ONLAINS Calendar</title>
    <link rel="stylesheet" href="../css/base.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../css/calendar.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../css/timeslots.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../css/procedures.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../css/bookings.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../css/admin.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <div class="container">
        <div class="user-options">
            <a href="../logout.php" class="logout-link"><i class="fas fa-sign-out-alt"></i> Izlogoties</a>
        </div>
    </div>

    <?php if (isset($_SESSION['booking_message'])): ?>
        <div class="message">
            <?php echo htmlspecialchars($_SESSION['booking_message']); unset($_SESSION['booking_message']); ?>
        </div>
    <?php endif; ?>

    <?php include '../includes/calendar.php'; ?>
    <?php include '../includes/timeslots.php'; ?>
    <?php include '../includes/procedures.php'; ?>
    <?php include '../includes/bookings.php'; ?>
    <?php include '../includes/footer.php'; ?>
</body>
</html>