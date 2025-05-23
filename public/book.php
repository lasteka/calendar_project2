<?php
require_once '../middleware.php';
runMiddleware();
require_once '../config/db_connection.php';
require_once '../includes/availability_functions.php'; // For slot validation

$is_logged_in = isset($_SESSION['user_id']);
$user_id_for_booking = null;
$user_name_for_booking = null;
$phone_for_booking = null;
// Guest details are distinct
$guest_name_input = null; 
$guest_phone_input = null;

if ($is_logged_in) {
    $user_id_for_booking = (int)$_SESSION['user_id'];
    $stmt_user = $pdo->prepare("SELECT name, phone FROM users WHERE id = ?");
    $stmt_user->execute([$user_id_for_booking]);
    $user_data = $stmt_user->fetch();
    if (!$user_data) {
        // This case (session exists but user not in DB) should ideally not happen
        $_SESSION['booking_message'] = "Lietotāja kļūda!";
        header("Location: index.php"); // Or logout.php
        exit;
    }
    $user_name_for_booking = $user_data['name'];
    $phone_for_booking = $user_data['phone'];
}
// Guest details will be fetched from $_POST later if it's a guest booking

// Apstrādājam rezervāciju
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'book_service') {
    $service_id = isset($_POST['service_id']) ? (int)$_POST['service_id'] : null;
    $selected_slot = $_POST['slot'] ?? null;
    $booking_date = $_POST['booking_date'] ?? null;

    if (!$is_logged_in) {
        $guest_name_input = $_POST['guest_name'] ?? null;
        $guest_phone_input = $_POST['guest_phone'] ?? null;
        if (empty($guest_name_input) || empty($guest_phone_input)) {
            $_SESSION['booking_message'] = "Viesim jānorāda vārds un tālrunis!";
            // Construct redirect URL to show the form again
            $redirect_url = "index.php?action=show_services&slot=" . urlencode($selected_slot ?? '') . 
                            "&selected_date=" . urlencode($booking_date ?? '') . 
                            "&service_id=" . ($service_id ?? '');
            // Include month and year if available in POST from the form, otherwise they might need to be recalculated or handled
            if(isset($_POST['month'])) $redirect_url .= "&month=" . urlencode($_POST['month']);
            if(isset($_POST['year'])) $redirect_url .= "&year=" . urlencode($_POST['year']);
            header("Location: " . $redirect_url);
            exit;
        }
    }
    
    // Basic validation for common fields
    if (empty($service_id) || empty($selected_slot) || empty($booking_date)) {
        $_SESSION['booking_message'] = "Nepieciešami visi rezervācijas dati!";
        // Redirect back, trying to preserve context
        $redirect_url = "index.php?";
        if ($booking_date) $redirect_url .= "selected_date=" . urlencode($booking_date);
        if ($service_id) $redirect_url .= "&service_id=" . $service_id;
        
        // If it was a guest trying to book, and action=show_services was set, preserve it
        // This helps to re-display the guest form
        if (!$is_logged_in && $selected_slot) { 
             $redirect_url .= "&action=show_services&slot=" . urlencode($selected_slot);
        }
        header("Location: " . $redirect_url);
        exit;
    }

    // Iegūstam pakalpojuma ilgumu (still needed for validation context)
    $stmt_service_check = $pdo->prepare("SELECT duration FROM services WHERE id = ?");
    $stmt_service_check->execute([$service_id]);
    $service_details = $stmt_service_check->fetch();
    if (!$service_details) {
        $_SESSION['booking_message'] = "Pakalpojums nav atrasts!";
        header("Location: index.php?selected_date=" . urlencode($booking_date));
        exit;
    }
    // $duration = $service_details['duration']; // Not directly used by getAvailableTimeslotsForService but good to have

    // Use getAvailableTimeslotsForService to check if the specific slot is available
    $all_day_available_slots = getAvailableTimeslotsForService($pdo, $booking_date, $service_id);
    
    $slot_is_genuinely_available = false;
    foreach ($all_day_available_slots as $day_part => $slots_in_part) {
        if (isset($slots_in_part['error'])) {
            $_SESSION['booking_message'] = "Kļūda pārbaudot laika slotu pieejamību: " . htmlspecialchars($slots_in_part['error']);
            header("Location: index.php?selected_date=" . urlencode($booking_date) . "&service_id=" . $service_id);
            exit;
        }
        if (is_array($slots_in_part)) { 
            foreach ($slots_in_part as $slot_info) { 
                $available_time = is_array($slot_info) ? $slot_info['time'] : $slot_info;
                if ($available_time === $selected_slot) {
                    $slot_is_genuinely_available = true;
                    break 2; 
                }
            }
        }
    }

    if (!$slot_is_genuinely_available) {
        $_SESSION['booking_message'] = "Izvēlētais laika slots vairs nav pieejams vai ir nederīgs. Lūdzu, izvēlieties citu.";
        header("Location: index.php?selected_date=" . urlencode($booking_date) . "&service_id=" . $service_id);
        exit;
    }

    // Ievietojam rezervāciju
    if ($is_logged_in) {
        $stmt_insert = $pdo->prepare(
            "INSERT INTO bookings (service_id, time_slot, booking_date, user_name, phone, user_id) 
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        $success = $stmt_insert->execute([
            $service_id, $selected_slot, $booking_date, 
            $user_name_for_booking, $phone_for_booking, $user_id_for_booking
        ]);
    } else { // Guest booking
        $stmt_insert = $pdo->prepare(
            "INSERT INTO bookings (service_id, time_slot, booking_date, guest_name, guest_phone) 
             VALUES (?, ?, ?, ?, ?)"
        );
        $success = $stmt_insert->execute([
            $service_id, $selected_slot, $booking_date, 
            $guest_name_input, $guest_phone_input // Use the input variables
        ]);
    }

    if ($success) {
        $_SESSION['booking_message'] = "Rezervācija veiksmīga!";
    } else {
        $errorInfo = $stmt_insert->errorInfo();
        $_SESSION['booking_message'] = "Rezervācijas kļūda: " . ($errorInfo[2] ?? 'Nezināma kļūda');
    }
    header("Location: index.php?selected_date=" . urlencode($booking_date));
    exit;

} else {
    // Ja forma nav iesniegta vai action nav pareizs, pāradresējam uz index.php
    header("Location: index.php");
    exit;
}
?>