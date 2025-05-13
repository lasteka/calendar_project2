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

// Ģenerējam CSRF token, ja tas vēl nav iestatīts
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Iegūstam rezervācijas
try {
    $stmt = $pdo->query("
        SELECT b.*, s.name AS service_name, u.email AS user_email, u.name AS user_name, u.phone AS user_phone
        FROM bookings b 
        LEFT JOIN services s ON b.service_id = s.id 
        LEFT JOIN users u ON b.user_id = u.id 
        ORDER BY b.booking_date, b.time_slot
    ");
    $bookings = $stmt->fetchAll();
} catch (PDOException $e) {
    echo "Kļūda: " . $e->getMessage();
    exit;
}
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
        <?php
        // Parādām ziņojumu, ja tāds ir
        if (isset($_SESSION['message'])) {
            $message_class = isset($_SESSION['message_type']) && $_SESSION['message_type'] === 'error' ? 'error' : 'success';
            echo '<div class="message ' . $message_class . '">' . htmlspecialchars($_SESSION['message']) . '</div>';
            // Notīrām ziņojumu pēc parādīšanas
            unset($_SESSION['message']);
            unset($_SESSION['message_type']);
        }
        ?>
        <div class="user-options">
            <a href="add_service.php" class="action-link"><i class="fas fa-plus"></i> Pievienot Pakalpojumu</a>
            <a href="services.php" class="action-link"><i class="fas fa-cogs"></i> Pārvaldīt Pakalpojumus</a>
            <a href="add_user.php" class="action-link"><i class="fas fa-user-plus"></i> Pievienot Lietotāju</a>
            <a href="timeslots.php" class="action-link"><i class="fas fa-clock"></i> Pārvaldīt Laika Slotus</a>
            <a href="../logout.php" class="logout-link"><i class="fas fa-sign-out-alt"></i> Izlogoties</a>
        </div>
    </div>

    <div class="table-container">
        <table class="table">
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
                <?php foreach ($bookings as $row): ?>
                    <tr>
                        <td><?= $row['id']; ?></td>
                        <td><?= htmlspecialchars($row['service_name']) . " (ID: " . $row['service_id'] . ")"; ?></td>
                        <td><?= $row['booking_date']; ?></td>
                        <td><?= $row['time_slot']; ?></td>
                        <td><?= htmlspecialchars($row['user_name']); ?></td>
                        <td><?= htmlspecialchars($row['user_phone']); ?></td>
                        <td><?= htmlspecialchars($row['user_email']); ?></td>
                        <td>
                            <a href="edit.php?id=<?= $row['id']; ?>" class="edit-link">Rediģēt</a>
                            <a href="delete.php?id=<?= $row['id']; ?>&csrf_token=<?= htmlspecialchars($_SESSION['csrf_token']); ?>" class="delete-btn" onclick="return confirm('Vai tiešām vēlaties dzēst šo rezervāciju?')">Dzēst</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php include '../includes/footer.php'; ?>
</body>
</html>