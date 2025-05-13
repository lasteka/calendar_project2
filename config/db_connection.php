<?php
try {
    $pdo = new PDO(
        "mysql:host=localhost;dbname=calendar_db", 
        "root", 
        "", 
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    die("Savienojuma kļūda: " . $e->getMessage());
}
?>