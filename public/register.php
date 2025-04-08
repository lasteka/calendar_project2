<?php
require_once '../middleware.php';
runMiddleware();

require_once '../config/db_connection.php';

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$registerMessage = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
            $registerMessage = "Reģistrācija veiksmīga! Lūdzu, ielogojies.";
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
    <link rel="stylesheet" href="../css/base.css">
</head>
<body>
    <div class="container">
        <h1>Reģistrēties</h1>
        <?php if ($registerMessage): ?>
            <div class="message">
                <?php echo htmlspecialchars($registerMessage); ?>
                <?php if (strpos($registerMessage, "veiksmīga") !== false): ?>
                    <p><a href="login.php">Ielogoties</a></p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        <form method="POST" action="register.php">
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
    </div>
</body>
</html>