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
$result = $conn->query("
    SELECT b.*, s.name AS service_name, u.email AS user_email 
    FROM bookings b 
    LEFT JOIN services s ON b.service_id = s.id 
    LEFT JOIN users u ON b.user_id = u.id 
    ORDER BY b.booking_date, b.time_slot
");
?>
<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <title>Admin Panelis – Rezervācijas</title>
    <link rel="stylesheet" href="../css/base.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../css/admin.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <div class="container">
        <h1>Visas rezervācijas</h1>
        <div class="user-options">
            <a href="add_service.php" class="action-link"><i class="fas fa-plus"></i> Pievienot Pakalpojumu</a>
            <a href="../logout.php" class="logout-link"><i class="fas fa-sign-out-alt"></i> Izlogoties</a>
        </div>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Pakalpojums</th>
                    <th>Datums</th>
                    <th>Laiks</th>
                    <th>Klienta vārds</th>
                    <th>Telefona numurs</th>
                    <th>Lietotāja e-pasts</th>
                    <th>Darbības</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['id']; ?></td>
                        <td><?= htmlspecialchars($row['service_name']) . " (ID: " . $row['service_id'] . ")"; ?></td>
                        <td><?= $row['booking_date']; ?></td>
                        <td><?= $row['time_slot']; ?></td>
                        <td><?= htmlspecialchars($row['user_name']); ?></td>
                        <td><?= htmlspecialchars($row['phone']); ?></td>
                        <td><?= htmlspecialchars($row['user_email']); ?></td>
                        <td><a href="edit.php?id=<?= $row['id']; ?>">Rediģēt</a></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <?php include '../includes/footer.php'; ?>
</body>
</html>