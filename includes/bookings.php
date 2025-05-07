<div class="bookings-list">
    <h2>Jūsu rezervācijas</h2>
    <?php
    if (!isset($_SESSION['user_id'])) {
        echo "<p>Lūdzu, ielogojieties, lai redzētu savas rezervācijas.</p>";
    } else {
        $user_id = (int)$_SESSION['user_id'];
        $stmt = $pdo->prepare("SELECT b.*, s.name AS service_name FROM bookings b LEFT JOIN services s ON b.service_id = s.id WHERE b.user_id = ? ORDER BY b.booking_date, b.time_slot");
        $stmt->execute([$user_id]);
        $bookings = $stmt->fetchAll();

        if (!$bookings) {
            echo "<p>Kļūda rezervāciju ielādē.</p>";
        } elseif (count($bookings) > 0) {
            foreach ($bookings as $booking) {
                echo "<div class='booking'>";
                echo "<p>Pakalpojums: " . htmlspecialchars($booking['service_name']) . " (ID: " . htmlspecialchars($booking['service_id']) . ") | Datums: " . htmlspecialchars($booking['booking_date']) . " | Laiks: " . htmlspecialchars($booking['time_slot']) . " | Vārds: " . htmlspecialchars($booking['user_name']) . "</p>";
                echo "<a href='?action=cancel_booking&booking_id=" . $booking['id'] . "&month=" . $month . "&year=" . $year . "&selected_date=" . urlencode($selected_date) . "' class='cancel-btn'>Atcelt rezervāciju</a>";
                echo "</div>";
            }
        } else {
            echo "<p>Nav rezervāciju.</p>";
        }
    }
    ?>
</div>