<?php
require_once '../middleware.php';
runMiddleware();

// Pārbaudām, vai lietotājs jau ir ielogojies
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header("Location: index.php");
    exit;
}

// Vienkārša paroles pārbaude (šeit izmantojam statisku paroli, bet reālā sistēmā jāizmanto datubāze)
$correctPassword = "admin123"; // Pielāgo pēc vajadzības
$loginError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    if ($password === $correctPassword) {
        $_SESSION['logged_in'] = true;
        header("Location: index.php");
        exit;
    } else {
        $loginError = "Nepareiza parole!";
    }
}
?>
<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <title>Admin Ielogoties</title>
    <link rel="stylesheet" href="../css/base.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../css/admin.css?v=<?php echo time(); ?>">
</head>
<body>
    <div class="container">
        <h1>Ielogoties Admin Panelī</h1>
        <?php if ($loginError): ?>
            <div class="message" style="color: red;">
                <?php echo htmlspecialchars($loginError); ?>
            </div>
        <?php endif; ?>
        <form method="POST" action="login.php">
            <div class="form-group">
                <label for="password">Parole:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit">Ielogoties</button>
        </form>
    </div>
</body>
</html>