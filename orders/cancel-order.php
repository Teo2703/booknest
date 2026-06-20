<?php
include '../app.php';
requireCustomer();

$user_id = (int)$_SESSION['user_id'];
$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($order_id <= 0) {
    header("Location: order-history.php");
    exit();
}

// Check the order belongs to this customer and is still cancellable
$stmt = $conn->prepare("SELECT order_id, status FROM orders WHERE order_id = ? AND user_id = ?");
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    header("Location: order-history.php");
    exit();
}

if (!in_array($order['status'], ['Pending', 'Processing'])) {
    header("Location: order-history.php?cancel_error=1");
    exit();
}

try {
    $conn->begin_transaction();

    // Restore stock for every book in this order
    $itemsStmt = $conn->prepare("SELECT book_id, quantity FROM order_items WHERE order_id = ?");
    $itemsStmt->bind_param("i", $order_id);
    $itemsStmt->execute();
    $items = $itemsStmt->get_result();

    $restoreStmt = $conn->prepare("UPDATE books SET stock = stock + ? WHERE book_id = ?");
    while ($item = $items->fetch_assoc()) {
        $restoreStmt->bind_param("ii", $item['quantity'], $item['book_id']);
        $restoreStmt->execute();
    }

    // Update order status
    $updateStmt = $conn->prepare("UPDATE orders SET status = 'Cancelled' WHERE order_id = ?");
    $updateStmt->bind_param("i", $order_id);
    $updateStmt->execute();

    // Log in status history
    $historyStmt = $conn->prepare("
        INSERT INTO order_status_history (order_id, status, changed_by_user_id, note)
        VALUES (?, 'Cancelled', ?, 'Cancelled by customer')
    ");
    $historyStmt->bind_param("ii", $order_id, $user_id);
    $historyStmt->execute();

    $conn->commit();
    header("Location: order-history.php?cancelled=" . $order_id);
    exit();

} catch (Exception $e) {
    $conn->rollback();
    header("Location: order-history.php?cancel_error=1");
    exit();
}
?>