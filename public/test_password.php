<?php
require_once '../config/db_connection.php';

$email = 'test@gmail.com'; // Aizstājiet ar reģistrēto e-pastu
$password = '123456'; // Aizstājiet ar paroli, ko izmantojāt reģistrācijā

try {
    $stmt = $pdo->prepare("SELECT password FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    if ($user) {
        echo "Hash from database: " . $user['password'] . "<br>";
        if (password_verify($password, $user['password'])) {
            echo "Password verification: SUCCESS";
        } else {
            echo "Password verification: FAILED";
        }
    } else {
        echo "User not found with email: $email";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>