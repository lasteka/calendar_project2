<?php
require_once '../middleware.php';
runMiddleware();

require_once '../config/db_connection.php';

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$loginError = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    $stmt = $conn->prepare("SELECT id, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($user = $result->fetch_assoc()) {
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            header("Location: index.php");
            exit;
        } else {
            $loginError = "Nepareizs e-pasts vai parole!";
        }
    } else {
        $loginError = "Nepareizs e-pasts vai parole!";
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <title>Ielogoties</title>
    <link rel="stylesheet" href="../css/base.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../css/admin.css?v=<?php echo time(); ?>">
</head>
<body>
    <div class="container">
        <h1>Ielogoties</h1>
        <?php if ($loginError): ?>
            <div class="message" style="color: red;">
                <?php echo htmlspecialchars($loginError); ?>
            </div>
        <?php endif; ?>
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
    </div>
</body>
</html>