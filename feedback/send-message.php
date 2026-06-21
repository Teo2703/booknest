<?php
include '../app.php';
requireCustomer();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: customer-chat.php");
    exit();
}

$user_id = (int)$_SESSION['user_id'];
$message = trim($_POST['message'] ?? '');

if ($message === '') {
    header("Location: customer-chat.php?error=empty");
    exit();
}

$stmt = $conn->prepare("
    INSERT INTO customer_messages 
    (user_id, sender_role, message, is_read) 
    VALUES (?, 'customer', ?, 0)
");

if (!$stmt) {
    die("Database prepare failed: " . $conn->error);
}

$stmt->bind_param("is", $user_id, $message);
$stmt->execute();

header("Location: customer-chat.php?sent=1");
exit();
?>