<?php
$host = 'localhost'; // Change if using a different database host
$dbname = 'iot';
$username = 'root'; // Change if using a different database user
$password = ''; // Change if a password is set

$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4"); // Ensure proper character encoding
?>
