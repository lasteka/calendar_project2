<?php
session_start();
require_once '../middleware.php';
runMiddleware();
require_once '../config/db_connection.php';

// Pārbaudām, vai lietotājs ir administrators
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// Pārbaudām, vai GET parametrā ir rezervācijas ID
if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$bookingId = (int)$_GET['id'];

// Apstrādājam formu
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_booking') {
    $serviceId = (int)$_POST['service_id'];
    $bookingDate = $_POST['booking_date'];
    $timeSlot = $_POST['time_slot'];
    $userName = $_POST['user_name'];
    $phone = $_POST['phone'];

    try {
        $stmt = $pdo->prepare("UPDATE bookings SET service_id = ?, booking_date = ?, time_slot = ?, user_name = ?, phone = ? WHERE id = ?");
        if ($stmt->execute([$serviceId, $bookingDate, $timeSlot, $userName, $phone, $bookingId])) {
            $_SESSION['message'] = "Rezervācija veiksmīgi atjaunināta!";
        } else {
            $_SESSION['message'] = "Kļūda atjauninot rezervāciju!";
        }
    } catch (PDOException $e) {
        $_SESSION['message'] = "Kļūda: " . $e->getMessage();
    }
    header("Location: index.php");
    exit;
}

// Ielādējam rezervācijas datus
try {
    $stmt = $pdo->prepare("
        SELECT b.*, s.name AS service_name, u.email AS user_email 
        FROM bookings b 
        LEFT JOIN services s ON b.service_id = s.id 
        LEFT JOIN users u ON b.user_id = u.id 
        WHERE b.id = ?
    ");
    $stmt->execute([$bookingId]);
    $booking = $stmt->fetch();
    if (!$booking) {
        echo "Rezervācija ar ID {$bookingId} netika atrasta.";
        exit;
    }

    // Ielādējam pakalpojumus
    $stmt = $pdo->query("SELECT id, name FROM services ORDER BY name");
    $services = $stmt->fetchAll();
} catch (PDOException $e) {
    echo "Kļūda: " . $e->getMessage();
    exit;
}
?>
<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <title>Labot rezervāciju</title>
    <link rel="stylesheet" href="../css/base.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../css/admin.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <div class="container">
        <h1>Labot rezervāciju</h1>
        <?php if (isset($_SESSION['message'])): ?>
            <div class="message">
                <?php echo htmlspecialchars($_SESSION['message']); unset($_SESSION['message']); ?>
            </div>
        <?php endif; ?>
        <form method="POST" action="edit.php?id=<?php echo $bookingId; ?>">
            <input type="hidden" name="action" value="update_booking">
            <div class="form-group">
                <label for="service_id">Pakalpojums:</label>
                <select name="service_id" id="service_id" required>
                    <?php foreach ($services as $service): ?>
                        <option value="<?php echo $service['id']; ?>" <?php echo $booking['service_id'] == $service['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($service['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <p>Aktuālais pakalpojums: <?php echo htmlspecialchars($booking['service_name']); ?> (ID: <?php echo $booking['service_id']; ?>)</p>
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
            <div class="form-group">
                <label for="user_email">Lietotāja e-pasts:</label>
                <input type="text" name="user_email" id="user_email" value="<?php echo htmlspecialchars($booking['user_email']); ?>" readonly>
            </div>
            <button type="submit">Atjaunināt rezervāciju</button>
        </form>
        <a href="index.php">Atpakaļ uz admin paneli</a>
    </div>
    <?php include '../includes/footer.php'; ?>
</body>
</html>