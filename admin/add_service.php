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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
            $stmt = $pdo->prepare("INSERT INTO services (name, price, duration) VALUES (?, ?, ?)");
            if ($stmt->execute([$name, $price, $duration])) {
                $message = "Pakalpojums veiksmīgi pievienots!";
            } else {
                $message = "Kļūda pievienojot pakalpojumu!";
            }
        } catch (PDOException $e) {
            $message = "Kļūda: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <title>Pievienot Pakalpojumu</title>
    <link rel="stylesheet" href="../css/base.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../css/admin.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <div class="container">
        <h1>Pievienot Jaunu Pakalpojumu</h1>
        <div class="user-options">
            <a href="../logout.php" class="logout-link"><i class="fas fa-sign-out-alt"></i> Izlogoties</a>
        </div>
        <?php if ($message): ?>
            <div class="message">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        <form method="POST" action="add_service.php">
            <div class="form-group">
                <label for="name">Pakalpojuma Nosaukums:</label>
                <input type="text" id="name" name="name" required>
            </div>
            <div class="form-group">
                <label for="price">Cena (EUR):</label>
                <input type="number" id="price" name="price" step="0.01" min="0" required>
            </div>
            <div class="form-group">
                <label for="duration">Ilgums (minūtēs):</label>
                <input type="number" id="duration" name="duration" min="1" required>
            </div>
            <button type="submit">Pievienot Pakalpojumu</button>
        </form>
        <p><a href="index.php">Atpakaļ uz rezervācijām</a></p>
    </div>
    <?php include '../includes/footer.php'; ?>
</body>
</html>