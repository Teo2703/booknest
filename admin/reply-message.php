<?php
include '../app.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: customer-messages.php");
    exit();
}

$user_id = (int)($_POST['user_id'] ?? 0);
$message = trim($_POST['message'] ?? '');

if ($user_id <= 0) {
    header("Location: customer-messages.php");
    exit();
}

if ($message === '') {
    header("Location: customer-messages.php?user_id=" . $user_id . "&error=empty");
    exit();
}

$userStmt = $conn->prepare("
    SELECT user_id
    FROM users
    WHERE user_id = ?
    AND role = 'customer'
");

if (!$userStmt) {
    die("Database prepare failed: " . $conn->error);
}

$userStmt->bind_param("i", $user_id);
$userStmt->execute();
$customer = $userStmt->get_result()->fetch_assoc();

if (!$customer) {
    header("Location: customer-messages.php");
    exit();
}

$stmt = $conn->prepare("
    INSERT INTO customer_messages 
    (user_id, sender_role, message, is_read, created_at) 
    VALUES (?, 'admin', ?, 0, NOW())
");

if (!$stmt) {
    die("Database prepare failed: " . $conn->error);
}

$stmt->bind_param("is", $user_id, $message);
$stmt->execute();

header("Location: customer-messages.php?user_id=" . $user_id . "&replied=1");
exit();
?>