<?php
session_start();
require_once '../config/db_connection.php';

$message = '';
$show_login_form = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
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
        try {
            // Pārbaudām, vai lietotājvārds vai e-pasts jau eksistē
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            if ($stmt->fetch()) {
                $message = "Lietotājvārds vai e-pasts jau ir reģistrēts!";
            } else {
                // Šifrējam paroli
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // Pievienojam lietotāju
                $stmt = $pdo->prepare("INSERT INTO users (username, email, password, name, phone) VALUES (?, ?, ?, ?, ?)");
                if ($stmt->execute([$username, $email, $hashed_password, $name, $phone])) {
                    $message = "Reģistrācija veiksmīga! Lūdzu, ielogojieties.";
                    $show_login_form = true;
                } else {
                    $message = "Reģistrācijas kļūda!";
                }
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
    <title>Reģistrācija</title>
    <link rel="stylesheet" href="../css/base.css?v=<?php echo time(); ?>">
<link rel="stylesheet" href="../css/admin.css?v=<?php echo time(); ?>">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <div class="container">
        <h1>Reģistrācija</h1>
        <?php if ($message): ?>
            <div class="message">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        <?php if ($show_login_form): ?>
            <h2>Ielogoties</h2>
            <form method="POST" action="login.php">
                <div class="form-group">
                    <label for="email">E-pasts:</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="password">Parole:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit">Ielogoties</button>
            </form>
        <?php else: ?>
            <form method="POST" action="register.php">
                <input type="hidden" name="register" value="1">
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
                <button type="submit">Reģistrēties</button>
            </form>
            <p>Jau ir konts? <a href="login.php">Ielogojieties</a></p>
        <?php endif; ?>
    </div>
    <?php include '../includes/footer.php'; ?>
</body>
</html>