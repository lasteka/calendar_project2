<?php
// config/db_connection.php
// Iestata savienojumu ar datubāzi. Pielāgo jūsu datubāzes piekļuves datus!
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "calendar_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>