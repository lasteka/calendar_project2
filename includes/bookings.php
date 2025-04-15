<div class="bookings-list">
    <h2>Jūsu rezervācijas</h2>
    <?php
    if (!isset($_SESSION['user_id'])) {
        echo "<p>Lūdzu, ielogojieties, lai redzētu savas rezervācijas.</p>";
    } else {
        $userId = (int)$_SESSION['user_id'];
        $bookingsQuery = "SELECT b.*, s.name AS service_name FROM bookings b LEFT JOIN services s ON b.service_id = s.id WHERE b.user_id = ? ORDER BY b.booking_date, b.time_slot";
        $stmt = $conn->prepare($bookingsQuery);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $bookingsResult = $stmt->get_result();

        if (!$bookingsResult) {
            echo "<p>Kļūda rezervāciju ielādē: " . $conn->error . "</p>";
        } elseif ($bookingsResult->num_rows > 0) {
            while ($booking = $bookingsResult->fetch_assoc()) {
                echo "<div class='booking'>";
                echo "<p>Pakalpojums: " . htmlspecialchars($booking['service_name']) . " (ID: " . htmlspecialchars($booking['service_id']) . ") | Datums: " . htmlspecialchars($booking['booking_date']) . " | Laiks: " . htmlspecialchars($booking['time_slot']) . " | Vārds: " . htmlspecialchars($booking['user_name']) . "</p>";
                echo "<a href='?action=cancel_booking&booking_id=" . $booking['id'] . "&month=" . $month . "&year=" . $year . "&selected_date=" . $selectedDate . "' class='cancel-btn'>Atcelt rezervāciju</a>";
                echo "</div>";
            }
        } else {
            echo "<p>Nav rezervāciju.</p>";
        }
        $bookingsResult->free();
        $stmt->close();
    }
    ?>
</div>