<?php
include 'db.php';

$order_id = $_POST['order_id'];
$status = $_POST['status'];

$stmt = $conn->prepare("UPDATE orders SET status = ? WHERE order_id = ?");
$stmt->bind_param("si", $status, $order_id);

if ($stmt->execute()) {
    header("Location: manage-orders.php");
    exit();
} else {
    echo "Error updating order status.";
}
?>