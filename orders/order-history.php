<?php
include '../app.php';
requireCustomer();

$user_id = (int)$_SESSION['user_id'];
$placedOrderId = isset($_GET['placed']) ? (int)$_GET['placed'] : 0;

$sql = "
    SELECT 
        orders.order_id,
        orders.order_date,
        orders.total_amount,
        orders.status,
        COALESCE(SUM(order_items.quantity), 0) AS item_count,
        GROUP_CONCAT(CONCAT(books.title, ' x ', order_items.quantity) ORDER BY books.title SEPARATOR '||') AS item_list
    FROM orders
    LEFT JOIN order_items ON orders.order_id = order_items.order_id
    LEFT JOIN books ON order_items.book_id = books.book_id
    WHERE orders.user_id = ?
    GROUP BY orders.order_id, orders.order_date, orders.total_amount, orders.status
    ORDER BY orders.order_date DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$orders = $stmt->get_result();

$ordersList = [];

while ($row = $orders->fetch_assoc()) {
    $ordersList[] = $row;
}

$historyByOrder = [];

if (!empty($ordersList)) {
    $orderIds = array_column($ordersList, 'order_id');
    $placeholders = implode(',', array_fill(0, count($orderIds), '?'));
    $types = str_repeat('i', count($orderIds));

    $historySql = "
        SELECT 
            order_status_history.order_id,
            order_status_history.status,
            order_status_history.changed_at,
            order_status_history.note,
            users.name AS changed_by_name
        FROM order_status_history
        LEFT JOIN users 
            ON order_status_history.changed_by_user_id = users.user_id
        WHERE order_status_history.order_id IN ($placeholders)
        ORDER BY order_status_history.changed_at ASC, order_status_history.history_id ASC
    ";

    $historyStmt = $conn->prepare($historySql);
    $historyStmt->bind_param($types, ...$orderIds);
    $historyStmt->execute();
    $historyResult = $historyStmt->get_result();

    while ($history = $historyResult->fetch_assoc()) {
        $historyByOrder[(int)$history['order_id']][] = $history;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order History | BookNest</title>
    <link rel="stylesheet" href="../css/style.css?v=123">
</head>
<body>

<div class="topbar">
    <div class="container">
        <span>Mini Online Bookstore</span>
        <span>Customer order history</span>
    </div>
</div>

<?php include __DIR__ . '/../includes/navigation.php'; ?>

<section class="page-title">
    <div class="container">
        <p class="eyebrow">Customer Orders</p>
        <h1>Order History</h1>
        <p>View your previous orders, ordered items, total amount, and current order status.</p>
    </div>
</section>

<main class="section">
    <div class="container">
        <?php if ($placedOrderId > 0): ?>
            <div class="notice" style="margin-bottom:1rem;">
                Order #BN<?php echo str_pad($placedOrderId, 4, '0', STR_PAD_LEFT); ?> placed successfully.
            </div>
        <?php endif; ?>

        <div class="table-wrap">
            <?php if (empty($ordersList)): ?>
                <div class="notice">
                    You have not placed any orders yet.
                    <a href="../books/books.php" style="font-weight:700;color:#7b4b2a;">Browse books now</a>.
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Date</th>
                            <th>Items</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Tracking</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ordersList as $order): ?>
                            <?php
                                $items = [];
                                if (!empty($order['item_list'])) {
                                    $items = explode('||', $order['item_list']);
                                }
                            ?>
                            <tr>
                                <td>#BN<?php echo str_pad($order['order_id'], 4, '0', STR_PAD_LEFT); ?></td>
                                <td><?php echo date("d M Y", strtotime($order['order_date'])); ?></td>
                                <td>
                                    <?php if (empty($items)): ?>
                                        <span class="small">No item details available</span>
                                    <?php else: ?>
                                        <?php foreach ($items as $item): ?>
                                            <?php echo htmlspecialchars($item); ?><br>
                                        <?php endforeach; ?>
                                        <span class="small">Total items: <?php echo (int)$order['item_count']; ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>RM<?php echo number_format($order['total_amount'], 2); ?></td>
                                <td>
                                    <span class="status <?php echo strtolower(trim($order['status'])); ?>">
                                        <?php echo htmlspecialchars($order['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <button 
                                        type="button" 
                                        class="btn secondary track-btn" 
                                        data-modal-target="track-modal-<?php echo (int)$order['order_id']; ?>"
                                    >
                                        Track
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php foreach ($ordersList as $order): ?>
                    <?php 
                        $orderId = (int)$order['order_id'];
                        $trackingHistory = $historyByOrder[$orderId] ?? [];
                    ?>

                    <div class="modal-overlay" id="track-modal-<?php echo $orderId; ?>">
                        <div class="tracking-modal">
                            <button type="button" class="modal-close" data-modal-close>&times;</button>

                            <p class="eyebrow">Order Tracking</p>
                            <h2>#BN<?php echo str_pad($orderId, 4, '0', STR_PAD_LEFT); ?></h2>

                            <div class="tracking-summary">
                                <div>
                                    <strong>Date</strong>
                                    <span><?php echo date("d M Y", strtotime($order['order_date'])); ?></span>
                                </div>
                                <div>
                                    <strong>Total</strong>
                                    <span>RM<?php echo number_format($order['total_amount'], 2); ?></span>
                                </div>
                                <div>
                                    <strong>Current Status</strong>
                                    <span class="status <?php echo strtolower(trim($order['status'])); ?>">
                                        <?php echo htmlspecialchars($order['status']); ?>
                                    </span>
                                </div>
                            </div>

                            <div class="timeline">
                                <?php if (empty($trackingHistory)): ?>
                                    <div class="notice">No tracking history available.</div>
                                <?php else: ?>
                                    <?php foreach ($trackingHistory as $history): ?>
                                        <div class="timeline-item <?php echo strtolower(trim($history['status'])); ?>">
                                            <div class="timeline-dot"></div>

                                            <div class="timeline-content">
                                                <strong><?php echo htmlspecialchars($history['status']); ?></strong>
                                                <p><?php echo date("d M Y, h:i A", strtotime($history['changed_at'])); ?></p>

                                                <?php if (!empty($history['note'])): ?>
                                                    <span class="small"><?php echo htmlspecialchars($history['note']); ?></span>
                                                <?php endif; ?>

                                                <span class="small">
                                                    Updated by: 
                                                    <?php echo htmlspecialchars($history['changed_by_name'] ?? 'System'); ?>
                                                </span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</main>

<footer class="footer">
    <div class="container footer-grid">
        <div>
            <h3>BookNest</h3>
            <p>Mini Online Bookstore e-commerce system.</p>
        </div>
        <div>
            <h4>Customer</h4>
            <a href="../books/books.php">Browse Books</a>
            <a href="cart.php">Shopping Cart</a>
            <a href="checkout.php">Checkout</a>
        </div>
        <div>
            <h4>Admin</h4>
            <a href="../admin/admin-dashboard.php">Dashboard</a>
            <a href="../admin/manage-books.php">Manage Books</a>
            <a href="../admin/manage-orders.php">Manage Orders</a>
        </div>
    </div>
</footer>
<script>
document.querySelectorAll('[data-modal-target]').forEach(function(button) {
    button.addEventListener('click', function() {
        const modalId = button.getAttribute('data-modal-target');
        const modal = document.getElementById(modalId);

        if (modal) {
            modal.classList.add('show');
        }
    });
});

document.querySelectorAll('[data-modal-close]').forEach(function(button) {
    button.addEventListener('click', function() {
        button.closest('.modal-overlay').classList.remove('show');
    });
});

document.querySelectorAll('.modal-overlay').forEach(function(overlay) {
    overlay.addEventListener('click', function(event) {
        if (event.target === overlay) {
            overlay.classList.remove('show');
        }
    });
});

document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        document.querySelectorAll('.modal-overlay.show').forEach(function(modal) {
            modal.classList.remove('show');
        });
    }
});
</script>
</body>
</html>