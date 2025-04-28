<?php
$host = "localhost"; // Change if needed
$user = "root"; // Change to your MySQL username
$password = "QWERT@!12"; // Change to your MySQL password
$database = "mlm_db"; // Change to your database name

$conn = new mysqli($host, $user, $password, $database);

// Check for connection error
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
