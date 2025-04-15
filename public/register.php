<?php
require_once '../middleware.php';
runMiddleware();

require_once '../config/db_connection.php';

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$registerMessage = '';
$registrationSuccess = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'register') {
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // Pārbaudām, vai lietotājvārds vai e-pasts jau eksistē
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $registerMessage = "Lietotājvārds vai e-pasts jau ir reģistrēts!";
    } else {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $email, $hashedPassword);
        if ($stmt->execute()) {
            $registerMessage = "Reģistrācija veiksmīga! Ielogojies zemāk.";
            $registrationSuccess = true;
        } else {
            $registerMessage = "Reģistrācija neizdevās: " . $stmt->error;
        }
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <title>Reģistrēties</title>
    <link rel="stylesheet" href="../css/base.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../css/admin.css?v=<?php echo time(); ?>">
</head>
<body>
    <div class="container">
        <?php if ($registrationSuccess): ?>
            <h1>Ielogoties</h1>
            <div class="message">
                <?php echo htmlspecialchars($registerMessage); ?>
            </div>
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
            <p>Nav konta? <a href="register.php">Reģistrēties</a></p>
        <?php else: ?>
            <h1>Reģistrēties</h1>
            <?php if ($registerMessage): ?>
                <div class="message">
                    <?php echo htmlspecialchars($registerMessage); ?>
                </div>
            <?php endif; ?>
            <form method="POST" action="register.php">
                <input type="hidden" name="action" value="register">
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
                <button type="submit">Reģistrēties</button>
            </form>
            <p>Jau ir konts? <a href="login.php">Ielogoties</a></p>
        <?php endif; ?>
    </div>
</body>
</html>