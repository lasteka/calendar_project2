<?php
require_once '../middleware.php';
runMiddleware();
require_once '../config/db_connection.php';

// Pārbaudām, vai lietotājs ir ielogojies
$is_logged_in = isset($_SESSION['user_id']);
$user_id = $is_logged_in ? (int)$_SESSION['user_id'] : null;
if ($is_logged_in) {
    $stmt = $pdo->prepare("SELECT name, phone FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    if (!$user) {
        header("Location: logout.php");
        exit;
    }
}

// Apstrādājam rezervācijas atcelšanu (tikai reģistrētiem lietotājiem)
if ($is_logged_in && isset($_GET['action']) && $_GET['action'] === 'cancel_booking' && isset($_GET['booking_id'])) {
    $booking_id = (int)$_GET['booking_id'];
    $stmt = $pdo->prepare("DELETE FROM bookings WHERE id = ? AND user_id = ?");
    $stmt->execute([$booking_id, $user_id]);
    $_SESSION['booking_message'] = $stmt->rowCount() > 0 ? "Rezervācija atcelta!" : "Rezervācija netika atrasta!";
    header("Location: index.php?selected_date=" . urlencode($_GET['selected_date'] ?? date('Y-m-d')));
    exit;
}

// Kalendāra parametri
$month = isset($_GET['month']) ? sprintf("%02d", (int)$_GET['month']) : date('m');
$year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');
$timestamp = mktime(0, 0, 0, $month, 1, $year);
$daysInMonth = date('t', $timestamp);
$firstDayOfWeek = date('w', $timestamp);
$monthName = date('F', $timestamp);

// Navigācija uz iepriekšējo/nākamo mēnesi
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

// Izvēlētais datums
$selected_date = isset($_GET['selected_date']) ? $_GET['selected_date'] : date('Y-m-d');

// Iegūstam pakalpojumus
$stmt = $pdo->query("SELECT id, name, price, duration FROM services");
$services = $stmt->fetchAll();
if (empty($services)) {
    $_SESSION['booking_message'] = "Nav atrasti pakalpojumi datubāzē!";
}

// Iegūstam aktīvos laika slotus
$stmt = $pdo->prepare("SELECT id, day_part, time_slot FROM timeslots WHERE is_active = ? ORDER BY FIELD(day_part, 'Morning', 'Day', 'Evening'), STR_TO_DATE(time_slot, '%H:%i')");
$stmt->execute([1]);
$all_timeslots = $stmt->fetchAll();

// Diagnostika: pārbaudām laika slotus
if (empty($all_timeslots)) {
    $_SESSION['booking_message'] = "Nav atrasti aktīvi laika sloti datubāzē!";
}

// Grupējam laika slotus pa dienas daļām
$timeslots_by_daypart = [];
foreach ($all_timeslots as $slot) {
    $timeslots_by_daypart[$slot['day_part']][] = $slot;
}

// Iegūstam aizņemtās vietas izvēlētajā datumā
$stmt = $pdo->prepare("SELECT b.time_slot, s.duration FROM bookings b JOIN services s ON b.service_id = s.id WHERE b.booking_date = ?");
$stmt->execute([$selected_date]);
$booked_slots = $stmt->fetchAll();

// Filtrējam pieejamos laika slotus, ņemot vērā pakalpojuma ilgumu
$timeslots = ['Morning' => [], 'Day' => [], 'Evening' => []];
$selected_service_id = $_GET['service_id'] ?? null;
if ($selected_service_id) {
    // Iegūstam pakalpojuma ilgumu
    $stmt = $pdo->prepare("SELECT duration FROM services WHERE id = ?");
    $stmt->execute([$selected_service_id]);
    $service = $stmt->fetch();
    if (!$service) {
        $_SESSION['booking_message'] = "Izvēlētais pakalpojums nav atrasts!";
        header("Location: index.php?selected_date=" . urlencode($selected_date));
        exit;
    }
    $duration = $service['duration'];
    $required_slots = ceil($duration / 30);

    foreach ($timeslots_by_daypart as $day_part => $slots) {
        foreach ($slots as $slot) {
            $slot_time = $slot['time_slot'];
            $is_available = true;

            // Pārbaudām, vai visi nepieciešamie sloti ir pieejami
            for ($i = 0; $i < $required_slots; $i++) {
                $next_time = date('H:i', strtotime("$slot_time + " . ($i * 30) . " minutes"));
                // Pārbaudām, vai slots eksistē un ir aktīvs
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM timeslots WHERE time_slot = ? AND is_active = ?");
                $stmt->execute([$next_time, 1]);
                if ($stmt->fetchColumn() == 0) {
                    $is_available = false;
                    break;
                }

                // Pārbaudām, vai slots nav aizņemts
                foreach ($booked_slots as $booked) {
                    $booked_start = strtotime($booked['time_slot']);
                    $booked_end = strtotime("+{$booked['duration']} minutes", $booked_start);
                    $current_slot_time = strtotime($next_time);
                    if ($current_slot_time >= $booked_start && $current_slot_time < $booked_end) {
                        $is_available = false;
                        break;
                    }
                }
                if (!$is_available) {
                    break;
                }
            }

            if ($is_available) {
                $timeslots[$day_part][] = $slot_time;
            }
        }
    }

    // Diagnostika: pārbaudām rezultātu
    if (empty($timeslots['Morning']) && empty($timeslots['Day']) && empty($timeslots['Evening'])) {
        $_SESSION['booking_message'] = "Nav pieejamu laika slotu izvēlētajam pakalpojumam un datumam! Iespējams, visi sloti ir aizņemts vai filtrēšana ir pārāk stingra.";
    }
}

// Pakalpojumu un laika slota izvēle
$show_services = isset($_GET['action']) && $_GET['action'] === 'show_services' && isset($_GET['slot']);
$selected_slot = $show_services ? $_GET['slot'] : null;
$guest_booking = !$is_logged_in && $show_services; // Režīms viesu rezervācijai
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
        <?php if ($is_logged_in): ?>
            <div class="user-options">
                <a href="../logout.php" class="logout-link"><i class="fas fa-sign-out-alt"></i> Izlogoties</a>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['booking_message'])): ?>
            <div class="message">
                <?php echo htmlspecialchars($_SESSION['booking_message']); unset($_SESSION['booking_message']); ?>
            </div>
        <?php endif; ?>

        <!-- Kalendāra sadaļa -->
        <?php include '../includes/calendar.php'; ?>

        <!-- Pakalpojumu izvēle -->
        <?php if (!$show_services): ?>
            <div class="service-selection">
                <h3>Izvēlieties pakalpojumu</h3>
                <?php if (empty($services)): ?>
                    <p>Nav pieejamu pakalpojumu. Lūdzu, sazinieties ar administratoru.</p>
                <?php else: ?>
                    <form action="index.php" method="GET">
                        <input type="hidden" name="selected_date" value="<?php echo htmlspecialchars($selected_date); ?>">
                        <input type="hidden" name="month" value="<?php echo $month; ?>">
                        <input type="hidden" name="year" value="<?php echo $year; ?>">
                        <div class="accordion">
                            <?php foreach ($services as $service): ?>
                                <div class="accordion-header">
                                    <span class="procedure-name"><?= htmlspecialchars($service['name']); ?></span>
                                    <span class="arrow">▼</span>
                                </div>
                                <div class="accordion-content">
                                    <div class="procedure">
                                        <input type="radio" name="service_id" id="service_<?= $service['id']; ?>" value="<?= $service['id']; ?>" 
                                               <?php echo $selected_service_id == $service['id'] ? 'checked' : ''; ?> onchange="this.form.submit()">
                                        <label for="service_<?= $service['id']; ?>" class="procedure-info">
                                            <span class="procedure-name"><?= htmlspecialchars($service['name']); ?></span>
                                            <span class="procedure-price"><?= number_format($service['price'], 2); ?> €, <?= $service['duration']; ?> min</span>
                                        </label>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Laika slotu izvēle -->
        <?php if ($selected_service_id && !$show_services): ?>
            <div class="timeslots-section">
                <h3>Pieejamie laika sloti <?php echo htmlspecialchars($selected_date); ?></h3>
                <!-- Pagaidu diagnostika -->
                <pre><?php // print_r($timeslots); ?></pre>
                <?php include '../includes/timeslots.php'; ?>
            </div>
        <?php endif; ?>

        <!-- Pakalpojumu apstiprināšana -->
        <?php if ($show_services): ?>
            <?php if ($is_logged_in): ?>
                <?php include '../includes/procedures.php'; ?>
            <?php elseif ($guest_booking): ?>
                <div class="guest-booking">
                    <h3>Apstipriniet rezervāciju</h3>
                    <form method="POST" action="book.php">
                        <input type="hidden" name="action" value="book_service">
                        <input type="hidden" name="slot" value="<?= htmlspecialchars($selected_slot); ?>">
                        <input type="hidden" name="booking_date" value="<?= htmlspecialchars($selected_date); ?>">
                        <input type="hidden" name="month" value="<?= htmlspecialchars($month); ?>">
                        <input type="hidden" name="year" value="<?= htmlspecialchars($year); ?>">
                        <div class="form-group">
                            <label for="guest_name">Vārds:*</label>
                            <input type="text" id="guest_name" name="guest_name" required>
                        </div>
                        <div class="form-group">
                            <label for="guest_phone">Tālrunis:*</label>
                            <input type="tel" id="guest_phone" name="guest_phone" pattern="[0-9+]{8,15}" required placeholder="+371XXXXXXXXX">
                        </div>
                        <button type="submit">Rezervēt</button>
                    </form>
                    <p>* Obligāti aizpildāmi lauki. Pēc rezervācijas jums būs jāpiesakās vai jāreģistrējas, lai pārvaldītu rezervācijas.</p>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <!-- Lietotāja rezervācijas (tikai reģistrētiem lietotājiem) -->
        <?php if ($is_logged_in): ?>
            <?php include '../includes/bookings.php'; ?>
        <?php endif; ?>
    </div>
    <?php include '../includes/footer.php'; ?>
    <script src="../js/accordion.js"></script>
</body>
</html>