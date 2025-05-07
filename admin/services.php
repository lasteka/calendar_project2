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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_service'])) {
    $id = (int)$_POST['id'];
    $name = trim($_POST['name'] ?? '');
    $price = filter_var($_POST['price'] ?? 0, FILTER_VALIDATE_FLOAT);
    $duration = filter_var($_POST['duration'] ?? 0, FILTER_VALIDATE_INT);

    if (empty($name)) {
        $message = "Pakalpojuma nosaukums ir obligāts!";
    } elseif ($price === false || $price < 0) {
        $message = "Norādiet derīgu cenu (pozitīvs skaitlis)!";
    } elseif ($duration === false || $duration <= 0) {
        $message = "Norādiet derīgu ilgumu (pozitīvs skaitlis minūtēs)!";
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE services SET name = ?, price = ?, duration = ? WHERE id = ?");
            if ($stmt->execute([$name, $price, $duration, $id])) {
                $message = "Pakalpojums veiksmīgi atjaunināts!";
            } else {
                $message = "Kļūda atjauninot pakalpojumu!";
            }
        } catch (PDOException $e) {
            $message = "Kļūda: " . $e->getMessage();
        }
    }
}

$edit_service = null;
if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    try {
        $stmt = $pdo->prepare("SELECT id, name, price, duration FROM services WHERE id = ?");
        $stmt->execute([$edit_id]);
        $edit_service = $stmt->fetch();
    } catch (PDOException $e) {
        $message = "Kļūda: " . $e->getMessage();
    }
}

try {
    $stmt = $pdo->query("SELECT id, name, price, duration FROM services ORDER BY name");
    $services = $stmt->fetchAll();
} catch (PDOException $e) {
    $message = "Kļūda: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <title>Pakalpojumu Pārskats</title>
    <link rel="stylesheet" href="../css/base.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../css/admin.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <div class="container">
        <h1>Pakalpojumu Pārskats</h1>
        <div class="user-options">
            <a href="add_service.php" class="action-link"><i class="fas fa-plus"></i> Pievienot Pakalpojumu</a>
            <a href="index.php" class="action-link"><i class="fas fa-list"></i> Atpakaļ uz Rezervācijām</a>
            <a href="timeslots.php" class="action-link"><i class="fas fa-clock"></i> Pārvaldīt Laika Slotus</a>
            <a href="../logout.php" class="logout-link"><i class="fas fa-sign-out-alt"></i> Izlogoties</a>
        </div>
        <?php if ($message): ?>
            <div class="message">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nosaukums</th>
                    <th>Cena (EUR)</th>
                    <th>Ilgums (min)</th>
                    <th>Darbības</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($services as $row): ?>
                    <tr>
                        <td><?= $row['id']; ?></td>
                        <td><?= htmlspecialchars($row['name']); ?></td>
                        <td><?= number_format($row['price'], 2); ?></td>
                        <td><?= $row['duration']; ?></td>
                        <td><a href="services.php?edit=<?= $row['id']; ?>">Rediģēt</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php if ($edit_service): ?>
            <h2>Rediģēt Pakalpojumu</h2>
            <form method="POST" action="services.php">
                <input type="hidden" name="id" value="<?= $edit_service['id']; ?>">
                <div class="form-group">
                    <label for="name">Pakalpojuma Nosaukums:</label>
                    <input type="text" id="name" name="name" value="<?= htmlspecialchars($edit_service['name']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="price">Cena (EUR):</label>
                    <input type="number" id="price" name="price" step="0.01" min="0" value="<?= $edit_service['price']; ?>" required>
                </div>
                <div class="form-group">
                    <label for="duration">Ilgums (minūtēs):</label>
                    <input type="number" id="duration" name="duration" min="1" value="<?= $edit_service['duration']; ?>" required>
                </div>
                <button type="submit" name="edit_service">Saglabāt Izmaiņas</button>
            </form>
        <?php endif; ?>
    </div>
    <?php include '../includes/footer.php'; ?>
</body>
</html>