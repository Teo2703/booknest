<?php
mysqli_report(MYSQLI_REPORT_OFF);

$host = "127.0.0.1";
$user = "root";
$password = "";
$database = "booknest";

$ports = [3306, 3307];
$conn = null;
$lastError = "";

foreach ($ports as $port) {
    $conn = @new mysqli($host, $user, $password, $database, $port);

    if (!$conn->connect_error) {
        $conn->set_charset("utf8mb4");
        break;
    }

    $lastError = $conn->connect_error;
    $conn = null;
}

if ($conn === null) {
    die("Database connection failed. Please check MySQL is running, database name is 'booknest', and port is 3306 or 3307. Error: " . $lastError);
}
?>