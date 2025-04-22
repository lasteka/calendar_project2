<?php
// Iekļaujam middleware un datubāzes savienojumu
require_once '../middleware.php';
runMiddleware();

require_once '../config/db_connection.php';

// Pārbaudām, vai lietotājs ir administrators
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

// Ziņojuma mainīgais
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Iegūstam datus
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    // Validējam ievadi
    if (empty($username)) {
        $message = "Lietotājvārds ir obligāts!";
    } elseif (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Norādiet derīgu e-pasta adresi!";
    } elseif (empty($password) || strlen($password) < 6) {
        $message = "Parolei jābūt vismaz 6 simbolus garai!";
    } elseif (empty($name)) {
        $message = "Vārds ir obligāts!";
    } elseif (empty($phone) || !preg_match("/^(\+?\d{1,4}[-.\s]?)?\d{8,}$/", $phone)) {
        $message = "Norādiet derīgu telefona numuru (vismaz 8 cipari)!";
    } else {
        // Pārbaudām, vai lietotājvārds vai e-pasts jau eksistē
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $message = "Lietotājvārds vai e-pasts jau ir reģistrēts!";
        } else {
            // Šifrējam paroli
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Pievienojam lietotāju
            $stmt = $conn->prepare("INSERT INTO users (username, email, password, name, phone) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $username, $email, $hashed_password, $name, $phone);
            if ($stmt->execute()) {
                $message = "Lietotājs veiksmīgi pievienots!";
            } else {
                $message = "Kļūda pievienojot lietotāju: " . $stmt->error;
            }
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <title>Pievienot Lietotāju</title>
    <link rel="stylesheet" href="../css/base.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../css/admin.css?v=<?php echo time(); ?>">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <div class="container">
        <h1>Pievienot Jaunu Lietotāju</h1>
        <div class="user-options">
            <a href="index.php" class="action-link"><i class="fas fa-list"></i> Atpakaļ uz Rezervācijām</a>
            <a href="../logout.php" class="logout-link"><i class="fas fa-sign-out-alt"></i> Izlogoties</a>
        </div>
        <?php if ($message): ?>
            <div class="message">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        <form method="POST" action="add_user.php">
            <div class="form-group">
                <label for="username">Lietotājvārds:</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="email">E-pasts:</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Parole:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group">
                <label for="name">Vārds:</label>
                <input type="text" id="name" name="name" required>
            </div>
            <div class="form-group">
                <label for="phone">Telefona numurs:</label>
                <input type="text" id="phone" name="phone" required placeholder="+371 12345678">
            </div>
            <button type="submit">Pievienot Lietotāju</button>
        </form>
        <p><a href="index.php">Atpakaļ uz rezervācijām</a></p>
    </div>
    <?php include '../includes/footer.php'; ?>
</body>
</html>