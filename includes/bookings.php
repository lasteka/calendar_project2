<?php
 ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>

<div class="bookings-list">
    <h2>Jūsu rezervācijas</h2>
    <?php
    if (!isset($_SESSION['user_id'])) {
        echo "<p>Lūdzu, ielogojieties, lai redzētu savas rezervācijas.</p>";
    } else {
        $userId = (int)$_SESSION['user_id'];
        $bookingsQuery = "SELECT * FROM bookings WHERE user_id = ? ORDER BY booking_date, time_slot";
        $stmt = $conn->prepare($bookingsQuery);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $bookingsResult = $stmt->get_result();

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
        $stmt->close();
    }
    ?>
</div>