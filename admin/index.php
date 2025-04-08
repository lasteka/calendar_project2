<?php
require_once '../middleware.php';
runMiddleware();

require_once '../config/db_connection.php';

// Pārbaudām, vai lietotājs ir ielogojies
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

// Iegūstam visus rezervāciju ierakstus
$result = $conn->query("SELECT * FROM bookings ORDER BY booking_date, time_slot");
?>
<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <title>Admin Panelis – Rezervācijas</title>
    <link rel="stylesheet" href="../css/base.css">
    <link rel="stylesheet" href="../css/bookings.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <div class="container">
        <h1>Visas rezervācijas</h1>
        <a href="logout.php">Izlogoties</a>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Pakalpojuma ID</th>
                    <th>Datums</th>
                    <th>Laiks</th>
                    <th>Klienta vārds</th>
                    <th>Telefona numurs</th>
                    <th>Darbības</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['id']; ?></td>
                        <td><?= $row['service_id']; ?></td>
                        <td><?= $row['booking_date']; ?></td>
                        <td><?= $row['time_slot']; ?></td>
                        <td><?= htmlspecialchars($row['user_name']); ?></td>
                        <td><?= htmlspecialchars($row['phone']); ?></td>
                        <td><a href="edit.php?id=<?= $row['id']; ?>">Rediģēt</a></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <?php include '../includes/footer.php'; ?>
</body>
</html>