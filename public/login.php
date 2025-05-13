<?php
// Ieslēdzam kļūdu attēlošanu
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Sākam sesiju
session_start();

// Iekļaujam middleware
if (!file_exists('../middleware.php')) {
    error_log("Error: middleware.php not found at " . __FILE__ . ":" . __LINE__);
    die("Error: middleware.php not found");
}
require_once '../middleware.php';
runMiddleware();

// Iekļaujam datubāzes savienojumu
if (!file_exists('../config/db_connection.php')) {
    error_log("Error: db_connection.php not found at " . __FILE__ . ":" . __LINE__);
    die("Error: db_connection.php not found");
}
require_once '../config/db_connection.php';

try {
    // Pārbaudām, vai lietotājs jau ir ielogojies
    if (isset($_SESSION['admin_id'])) {
        error_log("Redirecting already logged-in admin to admin/index.php");
        header("Location: ../admin/index.php");
        exit;
    } elseif (isset($_SESSION['user_id'])) {
        error_log("Redirecting already logged-in user to index.php");
        header("Location: index.php");
        exit;
    }

    $loginError = '';
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Normalizējam e-pastu (mazie burti, noņemam atstarpes)
        $email = trim(strtolower($_POST['email'] ?? ''));
        $password = $_POST['password'] ?? '';

        // Žurnalējam mēģinājumu un e-pasta HEX reprezentāciju
        $email_hex = bin2hex($email);
        error_log("Login attempt: email=$email, email_hex=$email_hex");

        // Pārbaudām admins tabulu
        $stmt = $pdo->prepare("SELECT id, email, password, HEX(email) AS email_hex FROM admins WHERE email = ?");
        $stmt->execute([$email]);
        $admin = $stmt->fetch();
        if ($admin) {
            error_log("Admin email found: email=" . $admin['email'] . ", email_hex=" . $admin['email_hex'] . ", password_hash=" . $admin['password']);
            if (strlen($admin['password']) < 60) {
                error_log("Invalid password hash length for admin: email=$email, length=" . strlen($admin['password']));
                $loginError = "Kļūda: Nederīgs paroles formāts!";
            } elseif (password_verify($password, $admin['password'])) {
                $_SESSION['admin_id'] = $admin['id'];
                error_log("Admin login successful: admin_id=" . $admin['id']);
                header("Location: ../admin/index.php");
                exit;
            } else {
                $loginError = "Nepareizs e-pasts vai parole!";
                error_log("Admin login failed: incorrect password for email=$email");
            }
        } else {
            // Pārbaudām users tabulu
            error_log("Admin email not found: email=$email, checking users table");
            $stmt = $pdo->prepare("SELECT id, email, password, HEX(email) AS email_hex FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            if ($user) {
                error_log("User email found: email=" . $user['email'] . ", email_hex=" . $user['email_hex'] . ", password_hash=" . $user['password']);
                if (strlen($user['password']) < 60) {
                    error_log("Invalid password hash length for user: email=$email, length=" . strlen($user['password']));
                    $loginError = "Kļūda: Nederīgs paroles formāts!";
                } elseif (password_verify($password, $user['password'])) {
                    $_SESSION['user_id'] = $user['id'];
                    error_log("User login successful: user_id=" . $user['id']);
                    header("Location: index.php");
                    exit;
                } else {
                    $loginError = "Nepareizs e-pasts vai parole!";
                    error_log("User login failed: incorrect password for email=$email");
                }
            } else {
                $loginError = "Nepareizs e-pasts vai parole!";
                error_log("Login failed: email=$email not found in admins or users");
            }
        }
    }
} catch (Exception $e) {
    error_log("Exception in login.php: " . $e->getMessage() . " at " . __FILE__ . ":" . __LINE__);
    $loginError = "Kļūda: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <title>Ielogoties</title>
    <link rel="stylesheet" href="../css/base.css?v=<?php echo time(); ?>">
<link rel="stylesheet" href="../css/public/auth.css?v=<?php echo time(); ?>">

</head>
<body>
    <?php include '../includes/header.php'; ?>
    <div class="container">
        <h1>Ielogoties</h1>
        <?php if ($loginError): ?>
            <div class="message error">
                <?php echo htmlspecialchars($loginError); ?>
            </div>
        <?php endif; ?>
        <form method="POST" action="login.php">
            <div class="form-group">
                <label for="email">E-pasts:</label>
                <input type="email" id="email" name="email" required value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>">
            </div>
            <div class="form-group">
                <label for="password">Parole:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit">Ielogoties</button>
        </form>
        <p>Nav konta? <a href="register.php">Reģistrēties</a></p>
    </div>
    <?php include '../includes/footer.php'; ?>
</body>
</html>