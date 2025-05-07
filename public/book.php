<?php
require_once '../middleware.php';
runMiddleware();
require_once '../config/db_connection.php';

// Pārbaudām, vai lietotājs ir ielogojies
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Iegūstam lietotāja datus
$user_id = (int)$_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT name, phone FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
if (!$user) {
    header("Location: logout.php");
    exit;
}

// Apstrādājam rezervāciju
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'book_service') {
    $service_id = (int)$_POST['service_id'];
    $selected_slot = $_POST['slot'];
    $booking_date = $_POST['booking_date'];
    $user_name = $user['name'];
    $phone = $user['phone'];

    // Validējam datus
    if (empty($service_id) || empty($selected_slot) || empty($booking_date)) {
        $_SESSION['booking_message'] = "Nepieciešami visi rezervācijas dati!";
        header("Location: index.php?selected_date=" . urlencode($booking_date));
        exit;
    }

    // Iegūstam pakalpojuma ilgumu
    $stmt = $pdo->prepare("SELECT duration FROM services WHERE id = ?");
    $stmt->execute([$service_id]);
    $service = $stmt->fetch();
    if (!$service) {
        $_SESSION['booking_message'] = "Pakalpojums nav atrasts!";
        header("Location: index.php?selected_date=" . urlencode($booking_date));
        exit;
    }
    $duration = $service['duration'];
    $required_slots = ceil($duration / 30);

    // Pārbaudām, vai visi nepieciešamie sloti ir pieejami un aktīvi
    $is_available = true;
    for ($i = 0; $i < $required_slots; $i++) {
        $next_time = date('H:i', strtotime("$selected_slot + " . ($i * 30) . " minutes"));
        // Pārbaudām, vai slots ir aktīvs
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM timeslots WHERE time_slot = ? AND is_active = 1");
        $stmt->execute([$next_time]);
        if ($stmt->fetchColumn() == 0) {
            $is_available = false;
            break;
        }

        // Pārbaudām, vai slots nav aizņemts
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE booking_date = ? AND time_slot = ?");
        $stmt->execute([$booking_date, $next_time]);
        if ($stmt->fetchColumn() > 0) {
            $is_available = false;
            break;
        }
    }

    if (!$is_available) {
        $_SESSION['booking_message'] = "Izvēlētais laika slots nav pieejams!";
        header("Location: index.php?selected_date=" . urlencode($booking_date));
        exit;
    }

    // Ievietojam rezervāciju
    $stmt = $pdo->prepare("INSERT INTO bookings (service_id, time_slot, booking_date, user_name, phone, user_id) VALUES (?, ?, ?, ?, ?, ?)");
    $success = $stmt->execute([$service_id, $selected_slot, $booking_date, $user_name, $phone, $user_id]);
    if ($success) {
        $_SESSION['booking_message'] = "Rezervācija veiksmīga!";
    } else {
        $_SESSION['booking_message'] = "Rezervācijas kļūda: " . $stmt->errorInfo()[2];
    }
    header("Location: index.php?selected_date=" . urlencode($booking_date));
    exit;
} else {
    // Ja forma nav iesniegta, pāradresējam uz index.php
    header("Location: index.php");
    exit;
}
?>