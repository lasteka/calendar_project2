<?php
// Sākam sesiju, ja tā vēl nav aktīva
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function runMiddleware() {
    // Uzturēšanas režīma pārbaude
    if (file_exists('maintenance.flag')) {
        header('HTTP/1.1 503 Service Unavailable');
        echo "Vietne šobrīd ir uzturēšanas režīmā. Lūdzu, mēģiniet vēlāk.";
        exit;
    }
}
?>