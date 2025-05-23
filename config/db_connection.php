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
    // Log the error for server records if possible (optional, advanced)
    // error_log("Database connection error: " . $e->getMessage()); 
            
    // Re-throw the exception to be handled by the calling script
    throw new PDOException("Savienojuma kļūda: " . $e->getMessage(), (int)$e->getCode());
}
?>