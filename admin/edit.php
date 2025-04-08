<?php
// admin/edit.php
require_once '../middleware.php';
runMiddleware();

require_once '../config/db_connection.php';

// Pārbaudām, vai GET parametrā ir rezervācijas ID
if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$bookingId = (int)$_GET['id'];

// Apstrādājam formu, ja tā tika iesniegta
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_booking') {
    $serviceId   = (int)$_POST['service_id'];
    $bookingDate = $_POST['booking_date'];
    $timeSlot    = $_POST['time_slot'];
    $userName    = $_POST['user_name'];
    $phone       = $_POST['phone'];

    $stmt = $conn->prepare("UPDATE bookings SET service_id = ?, booking_date = ?, time_slot = ?, user_name = ?, phone = ? WHERE id = ?");
    $stmt->bind_param("issssi", $serviceId, $bookingDate, $timeSlot, $userName, $phone, $bookingId);

    if ($stmt->execute()) {
        $message = "Rezervācija veiksmīgi atjaunināta!";
    } else {
        $message = "Kļūda atjauninot rezervāciju: " . $stmt->error;
    }
    $stmt->close();
}

// Ielādējam rezervācijas datus no datubāzes
$stmt = $conn->prepare("SELECT * FROM bookings WHERE id = ?");
$stmt->bind_param("i", $bookingId);
$stmt->execute();
$result = $stmt->get_result();
$booking = $result->fetch_assoc();
$stmt->close();

if (!$booking) {
    echo "Rezervācija ar ID {$bookingId} netika atrasta.";
    exit;
}
?>
<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <title>Labot rezervāciju</title>
    <link rel="stylesheet" href="../css/base.css">
    <!-- Papildu stili pēc vajadzības -->
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <div class="container">
        <h1>Labot rezervāciju</h1>
        <?php if (isset($message)): ?>
            <div class="message">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        <form method="POST" action="edit.php?id=<?php echo $bookingId; ?>">
            <input type="hidden" name="action" value="update_booking">
            <div class="form-group">
                <label for="service_id">Pakalpojuma ID:</label>
                <input type="number" name="service_id" id="service_id" value="<?php echo htmlspecialchars($booking['service_id']); ?>" required>
            </div>
            <div class="form-group">
                <label for="booking_date">Datums:</label>
                <input type="date" name="booking_date" id="booking_date" value="<?php echo htmlspecialchars($booking['booking_date']); ?>" required>
            </div>
            <div class="form-group">
                <label for="time_slot">Laiks:</label>
                <input type="text" name="time_slot" id="time_slot" value="<?php echo htmlspecialchars($booking['time_slot']); ?>" required>
            </div>
            <div class="form-group">
                <label for="user_name">Klienta vārds:</label>
                <input type="text" name="user_name" id="user_name" value="<?php echo htmlspecialchars($booking['user_name']); ?>" required>
            </div>
            <div class="form-group">
                <label for="phone">Telefona numurs:</label>
                <input type="text" name="phone" id="phone" value="<?php echo htmlspecialchars($booking['phone']); ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">Atjaunināt rezervāciju</button>
        </form>
        <a href="index.php">Atpakaļ uz admin paneli</a>
    </div>
    <?php include '../includes/footer.php'; ?>
</body>
</html>
