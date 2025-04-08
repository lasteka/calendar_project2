<?php
// Sākam sesiju
session_start();

function runMiddleware() {
    // Žurnāla ieraksts: saglabājam pieprasījuma laiku un URI
    $log = date('Y-m-d H:i:s') . " - " . $_SERVER['REQUEST_URI'] . "\n";
    if (!is_dir('logs')) {
        mkdir('logs', 0777, true);
    }
    file_put_contents('logs/request.log', $log, FILE_APPEND);

    // Uzturēšanas režīma pārbaude
    if (file_exists('maintenance.flag')) {
        header('HTTP/1.1 503 Service Unavailable');
        echo "Vietne šobrīd ir uzturēšanas režīmā. Lūdzu, mēģiniet vēlāk.";
        exit;
    }
}
?>