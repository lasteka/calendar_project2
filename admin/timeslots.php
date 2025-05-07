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

$message = '';

try {
    // Statusa maiņa
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'toggle_active') {
        $slot_id = (int)$_POST['slot_id'];
        $is_active = (int)$_POST['is_active'];

        $stmt = $pdo->prepare("UPDATE timeslots SET is_active = ? WHERE id = ?");
        if ($stmt->execute([$is_active ? 0 : 1, $slot_id])) {
            $message = "Laika slota statuss veiksmīgi atjaunināts!";
        } else {
            $message = "Kļūda, atjauninot laika slota statusu!";
        }
    }

    // Jauna slota pievienošana
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_slot') {
        $day_part = trim($_POST['day_part']);
        $time_slot = trim($_POST['time_slot']);
        $is_active = isset($_POST['is_active']) ? 1 : 0;

        if (empty($day_part) || empty($time_slot)) {
            $message = "Dienas daļa un laika slots ir obligāti!";
        } elseif (!in_array($day_part, ['Morning', 'Day', 'Evening'])) {
            $message = "Nederīga dienas daļa!";
        } elseif (!preg_match('/^([0-1][0-9]|2[0-3]):(00|15|30|45)$/', $time_slot)) {
            $message = "Laika slotam jābūt 00, 15, 30 vai 45 minūtēs!";
        } else {
            $stmt = $pdo->prepare("INSERT INTO timeslots (day_part, time_slot, is_active) VALUES (?, ?, ?)");
            if ($stmt->execute([$day_part, $time_slot, $is_active])) {
                $message = "Jauns laika slots veiksmīgi pievienots!";
            } else {
                $message = "Kļūda, pievienojot laika slotu!";
            }
        }
    }

    // Slota dzēšana
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_slot') {
        $slot_id = (int)$_POST['slot_id'];

        $stmt = $pdo->prepare("DELETE FROM timeslots WHERE id = ?");
        if ($stmt->execute([$slot_id])) {
            $message = "Laika slots veiksmīgi dzēsts!";
        } else {
            $message = "Kļūda, dzēšot laika slotu!";
        }
    }

    // Iegūstam laika slotus
    $stmt = $pdo->query("SELECT id, day_part, time_slot, is_active FROM timeslots ORDER BY FIELD(day_part, 'Morning', 'Day', 'Evening'), STR_TO_DATE(time_slot, '%H:%i')");
    $timeslots = $stmt->fetchAll();
} catch (PDOException $e) {
    $message = "Kļūda: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <title>Pārvaldīt Laika Slotus</title>
    <link rel="stylesheet" href="../css/base.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../css/admin.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <div class="container">
        <h1>Pārvaldīt Laika Slotus</h1>
        <div class="user-options">
            <a href="index.php" class="action-link"><i class="fas fa-list"></i> Atpakaļ uz Rezervācijām</a>
            <a href="add_service.php" class="action-link"><i class="fas fa-plus"></i> Pievienot Pakalpojumu</a>
            <a href="services.php" class="action-link"><i class="fas fa-cogs"></i> Pārvaldīt Pakalpojumus</a>
            <a href="add_user.php" class="action-link"><i class="fas fa-user-plus"></i> Pievienot Lietotāju</a>
            <a href="../logout.php" class="logout-link"><i class="fas fa-sign-out-alt"></i> Izlogoties</a>
        </div>
        <?php if ($message): ?>
            <div class="message">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        <h2>Pievienot Jaunu Laika Slotu</h2>
        <form method="POST" action="timeslots.php">
            <input type="hidden" name="action" value="add_slot">
            <div class="form-group">
                <label for="day_part">Dienas daļa:</label>
                <select id="day_part" name="day_part" required>
                    <option value="Morning">Rīts</option>
                    <option value="Day">Diena</option>
                    <option value="Evening">Vakars</option>
                </select>
            </div>
            <div class="form-group">
                <label for="time_slot">Laiks (HH:MM):</label>
                <input type="text" id="time_slot" name="time_slot" placeholder="HH:MM" required>
            </div>
            <div class="form-group">
                <label><input type="checkbox" name="is_active" checked> Aktīvs</label>
            </div>
            <button type="submit">Pievienot</button>
        </form>
        <h2>Laika Sloti</h2>
        <?php if (count($timeslots) > 0): ?>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Dienas daļa</th>
                        <th>Laiks</th>
                        <th>Statuss</th>
                        <th>Darbības</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($timeslots as $slot): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($slot['id']); ?></td>
                            <td><?php echo htmlspecialchars($slot['day_part']); ?></td>
                            <td><?php echo htmlspecialchars($slot['time_slot']); ?></td>
                            <td><?php echo $slot['is_active'] ? 'Aktīvs' : 'Neaktīvs'; ?></td>
                            <td>
                                <form method="POST" action="timeslots.php" style="display:inline;">
                                    <input type="hidden" name="action" value="toggle_active">
                                    <input type="hidden" name="slot_id" value="<?php echo $slot['id']; ?>">
                                    <input type="hidden" name="is_active" value="<?php echo $slot['is_active']; ?>">
                                    <button type="submit" class="toggle-btn">
                                        <?php echo $slot['is_active'] ? 'Izslēgt' : 'Ieslēgt'; ?>
                                    </button>
                                </form>
                                <form method="POST" action="timeslots.php" style="display:inline;">
                                    <input type="hidden" name="action" value="delete_slot">
                                    <input type="hidden" name="slot_id" value="<?php echo $slot['id']; ?>">
                                    <button type="submit" class="delete-btn" onclick="return confirm('Vai tiešām vēlaties dzēst šo laika slotu?');">
                                        Dzēst
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>Nav laika slotu.</p>
        <?php endif; ?>
    </div>
    <?php include '../includes/footer.php'; ?>
</body>
</html>