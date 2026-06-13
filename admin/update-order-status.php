<?php
include '../app.php';
requireAdmin();

$order_id = (int)$_POST['order_id'];
$status = $_POST['status'] ?? '';

$allowedStatus = ['Pending', 'Processing', 'Completed', 'Cancelled'];
if (!in_array($status, $allowedStatus)) {
    die('Invalid order status.');
}

$stmt = $conn->prepare("UPDATE orders SET status = ? WHERE order_id = ?");
$stmt->bind_param("si", $status, $order_id);

if ($stmt->execute()) {
    header("Location: manage-orders.php");
    exit();
} else {
    echo "Error updating order status.";
}
?>