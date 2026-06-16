<?php
include '../app.php';
requireAdmin();

$order_id = (int)($_POST['order_id'] ?? 0);
$status = $_POST['status'] ?? '';

$allowedStatus = ['Pending', 'Processing', 'Completed', 'Cancelled'];

if ($order_id <= 0 || !in_array($status, $allowedStatus)) {
    die('Invalid order status.');
}

try {
    $conn->begin_transaction();

    $stmt = $conn->prepare("SELECT status FROM orders WHERE order_id = ? FOR UPDATE");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $order = $stmt->get_result()->fetch_assoc();

    if (!$order) {
        throw new Exception("Order not found.");
    }

    $oldStatus = $order['status'];

    if ($oldStatus !== $status) {
        $updateStmt = $conn->prepare("UPDATE orders SET status = ? WHERE order_id = ?");
        $updateStmt->bind_param("si", $status, $order_id);
        $updateStmt->execute();

        $adminId = (int)$_SESSION['user_id'];
        $note = "Status changed from " . $oldStatus . " to " . $status;

        $historyStmt = $conn->prepare("
            INSERT INTO order_status_history (order_id, status, changed_by_user_id, note)
            VALUES (?, ?, ?, ?)
        ");
        $historyStmt->bind_param("isis", $order_id, $status, $adminId, $note);
        $historyStmt->execute();
    }

    $conn->commit();

    header("Location: manage-orders.php");
    exit();

} catch (Exception $e) {
    $conn->rollback();
    echo "Error updating order status.";
}
?>