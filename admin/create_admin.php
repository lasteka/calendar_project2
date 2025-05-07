<?php
// Iekļaujam datubāzes savienojumu
require_once '../config/db_connection.php';

// Administratora dati
$email = 'admin@gmail.com'; // Mainiet uz vēlamo e-pastu
$password = 'admin123'; // Mainiet uz vēlamo paroli

try {
    // Pārbaudām, vai e-pasts jau eksistē
    $stmt = $pdo->prepare("SELECT id FROM admins WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        echo "Kļūda: Administrators ar e-pastu $email jau eksistē!";
        exit;
    }

    // Šifrējam paroli
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Pievienojam administratoru
    $stmt = $pdo->prepare("INSERT INTO admins (email, password) VALUES (?, ?)");
    if ($stmt->execute([$email, $hashed_password])) {
        echo "Administrators veiksmīgi izveidots!<br>";
        echo "E-pasts: $email<br>";
        echo "Parole: $password<br>";
        echo "Lūdzu, ielogojieties: <a href='login.php'>Admin Login</a>";
    } else {
        echo "Kļūda pievienojot administratoru!";
    }
} catch (PDOException $e) {
    echo "Kļūda: " . $e->getMessage();
}
?>