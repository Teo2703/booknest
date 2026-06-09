<?php
$host = "localhost";
$user = "root";
$password = "";
$database = "booknest";

// Try default port (3306)
$conn = @new mysqli($host, $user, $password, $database);

// If failed, try 3307 (for your friend)
if ($conn->connect_error) {
    $conn = new mysqli($host, $user, $password, $database, 3307);
}

// If still fail → show error
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
?>