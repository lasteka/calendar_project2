<div class="bookings-list">
    <h2>Jūsu rezervācijas</h2>
    <?php
    $bookingsQuery = "SELECT * FROM bookings ORDER BY booking_date, time_slot";
    $bookingsResult = $conn->query($bookingsQuery);
    if (!$bookingsResult) {
        echo "<p>Kļūda rezervāciju ielādē: " . $conn->error . "</p>";
    } elseif ($bookingsResult->num_rows > 0) {
        while ($booking = $bookingsResult->fetch_assoc()) {
            echo "<div class='booking'>";
            echo "<p>Pakalpojums ID: " . htmlspecialchars($booking['service_id']) . " | Datums: " . htmlspecialchars($booking['booking_date']) . " | Laiks: " . htmlspecialchars($booking['time_slot']) . " | Vārds: " . htmlspecialchars($booking['user_name']) . "</p>";
            echo "<a href='?action=cancel_booking&booking_id=" . $booking['id'] . "&month=" . $month . "&year=" . $year . "&selected_date=" . $selectedDate . "' class='cancel-btn'>Atcelt rezervāciju</a>";
            echo "</div>";
        }
    } else {
        echo "<p>Nav rezervāciju.</p>";
    }
    $bookingsResult->free();
    ?>
</div>