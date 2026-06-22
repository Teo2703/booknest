<?php
include '../app.php';
requireCustomer();

$user_id = (int)$_SESSION['user_id'];
$placedOrderId = isset($_GET['placed']) ? (int)$_GET['placed'] : 0;

$sql = "
    SELECT order_id, order_date, total_amount, status
    FROM orders
    WHERE user_id = ?
    ORDER BY order_date DESC
";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Database prepare failed: " . $conn->error);
}

$stmt->bind_param("i", $user_id);
$stmt->execute();
$orders = $stmt->get_result();

$ordersList = [];

while ($row = $orders->fetch_assoc()) {
    $ordersList[] = $row;
}

$historyByOrder = [];
$latestRefundByOrder = [];
$itemsByOrder = [];
$reviewedBooks = [];

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

    if ($historyStmt) {
        $historyStmt->bind_param($types, ...$orderIds);
        $historyStmt->execute();
        $historyResult = $historyStmt->get_result();

        while ($history = $historyResult->fetch_assoc()) {
            $historyByOrder[(int)$history['order_id']][] = $history;
        }
    }

    $refundSql = "
        SELECT r.order_id, r.status
        FROM refunds r
        WHERE r.refund_id = (
            SELECT MAX(r2.refund_id)
            FROM refunds r2
            WHERE r2.order_id = r.order_id
        )
        AND r.order_id IN ($placeholders)
    ";

    $refundStmt = $conn->prepare($refundSql);

    if ($refundStmt) {
        $refundStmt->bind_param($types, ...$orderIds);
        $refundStmt->execute();
        $refundResult = $refundStmt->get_result();

        while ($refundRow = $refundResult->fetch_assoc()) {
            $latestRefundByOrder[(int)$refundRow['order_id']] = $refundRow['status'];
        }
    }

    // Fetch order items individually (with book_id) so we can link to each book's review form
    $itemsSql = "
        SELECT order_items.order_id, order_items.book_id, order_items.quantity, books.title
        FROM order_items
        JOIN books ON order_items.book_id = books.book_id
        WHERE order_items.order_id IN ($placeholders)
        ORDER BY books.title
    ";

    $itemsStmt = $conn->prepare($itemsSql);
    $itemsStmt->bind_param($types, ...$orderIds);
    $itemsStmt->execute();
    $itemsResult = $itemsStmt->get_result();

    while ($itemRow = $itemsResult->fetch_assoc()) {
        $itemsByOrder[(int)$itemRow['order_id']][] = $itemRow;
    }

    // Find which books in these orders already have a review, so we don't prompt twice
    $reviewSql = "
        SELECT order_id, book_id FROM reviews WHERE order_id IN ($placeholders)
    ";
    $reviewStmt = $conn->prepare($reviewSql);
    $reviewStmt->bind_param($types, ...$orderIds);
    $reviewStmt->execute();
    $reviewResult = $reviewStmt->get_result();

    while ($reviewRow = $reviewResult->fetch_assoc()) {
        $reviewedBooks[(int)$reviewRow['order_id']][(int)$reviewRow['book_id']] = true;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order History | BookNest</title>
    <link rel="stylesheet" href="../css/style.css?v=151">

    <style>
        .order-action-buttons {
            display: flex;
            flex-direction: column;
            gap: 0.45rem;
        }

        .order-action-buttons .btn,
        .receipt-btn {
            width: 100%;
            min-width: 110px;
            padding: 0.55rem 0.75rem;
            font-size: 0.9rem;
            text-align: center;
            box-sizing: border-box;
        }

        .receipt-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
        }

        .order-history-table th,
        .order-history-table td {
            vertical-align: top;
        }

        @media (max-width: 900px) {
            .table-wrap {
                overflow-x: auto;
            }

            .order-action-buttons {
                min-width: 120px;
            }
        }
    </style>
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
        <p>View your previous orders, ordered items, total amount, current order status, and receipt.</p>
    </div>
</section>

<main class="section">
    <div class="container">

        <?php if ($placedOrderId > 0): ?>
            <div class="notice" style="margin-bottom:1rem;">
                Order #BN<?php echo str_pad($placedOrderId, 4, '0', STR_PAD_LEFT); ?> placed successfully.
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['cancelled'])): ?>
            <div class="notice" style="margin-bottom:1rem;">
                Order #BN<?php echo str_pad((int)$_GET['cancelled'], 4, '0', STR_PAD_LEFT); ?> has been cancelled.
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['refund_requested'])): ?>
            <div class="notice" style="margin-bottom:1rem;">
                Refund request submitted for order #BN<?php echo str_pad((int)$_GET['refund_requested'], 4, '0', STR_PAD_LEFT); ?>. We'll review it shortly.
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['cancel_error'])): ?>
            <div class="notice" style="margin-bottom:1rem;">
                This order can no longer be cancelled.
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['refund_error'])): ?>
            <div class="notice" style="margin-bottom:1rem;">
                <?php
                if ($_GET['refund_error'] === 'already_requested') {
                    echo "A refund has already been requested for this order.";
                } else {
                    echo "This order is not eligible for a refund.";
                }
                ?>
            </div>
        <?php endif; ?>

        <div class="table-wrap">
            <?php if (empty($ordersList)): ?>

                <div class="notice">
                    You have not placed any orders yet.
                    <a href="../books/books.php" style="font-weight:700;color:#7b4b2a;">
                        Browse books now
                    </a>.
                </div>

            <?php else: ?>

                <table class="order-history-table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Date</th>
                            <th>Items</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Tracking</th>
                            <th>Receipt</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($ordersList as $order): ?>
                            <?php
                                $items = [];
                                if (!empty($order['item_list'])) {
                                    $items = explode('||', $order['item_list']);
                                }
                                $orderIdInt = (int)$order['order_id'];
                                $orderItems = $itemsByOrder[$orderIdInt] ?? [];
                                $latestRefundStatus = $latestRefundByOrder[$orderIdInt] ?? null;

                                $displayStatus = $order['status'];

                                if ($order['status'] === 'Completed') {
                                    if ($latestRefundStatus === 'Pending') {
                                        $displayStatus = 'Refund Pending';
                                    } elseif ($latestRefundStatus === 'Rejected') {
                                        $displayStatus = 'Refund Rejected';
                                    }
                                }

                                $displayStatusClass = strtolower(str_replace(' ', '-', trim($displayStatus)));
                            ?>

                            <tr>
                                <td>
                                    #BN<?php echo str_pad($order['order_id'], 4, '0', STR_PAD_LEFT); ?>
                                </td>

                                <td>
                                    <?php echo date("d M Y", strtotime($order['order_date'])); ?>
                                </td>

                                <td>
                                    <?php if (empty($orderItems)): ?>
                                        <span class="small">No item details available</span>
                                    <?php else: ?>
                                        <?php foreach ($orderItems as $item): ?>
                                            <?php $alreadyReviewed = isset($reviewedBooks[$orderIdInt][$item['book_id']]); ?>
                                            <div style="margin-bottom:0.3rem;">
                                                <?php echo htmlspecialchars($item['title']); ?> x <?php echo (int)$item['quantity']; ?>

                                                <?php if ($order['status'] === 'Completed'): ?>
                                                    <?php if ($alreadyReviewed): ?>
                                                        <span class="small" style="color:#1d9e75;">Reviewed</span>
                                                    <?php else: ?>
                                                        <a class="small" style="color:#7b4b2a;font-weight:700;"
                                                           href="../books/book-detail.php?id=<?php echo $item['book_id']; ?>#reviews">
                                                            Rate this book
                                                        </a>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>
                                        <?php
                                            $totalItems = 0;

                                            foreach ($orderItems as $item) {
                                                $totalItems += (int)$item['quantity'];
                                            }
                                            ?>

                                            <span class="small">Total items: <?php echo $totalItems; ?></span>
                                    <?php endif; ?>
                                </td>

                                <td>
                                    RM<?php echo number_format($order['total_amount'], 2); ?>
                                </td>

                                <td>
                                    <span class="status <?php echo htmlspecialchars($displayStatusClass); ?>">
                                        <?php echo htmlspecialchars($displayStatus); ?>
                                    </span>
                                </td>

                                <td>
                                    <div class="order-action-buttons">
                                        <button 
                                            type="button" 
                                            class="btn secondary track-btn" 
                                            data-modal-target="track-modal-<?php echo $orderIdInt; ?>"
                                        >
                                            Track
                                        </button>

                                        <?php if (in_array($order['status'], ['Pending', 'Processing'])): ?>
                                            <a 
                                                class="btn danger" 
                                                href="cancel-order.php?id=<?php echo $orderIdInt; ?>"
                                                onclick="return confirm('Cancel this order?')"
                                            >
                                                Cancel
                                            </a>
                                        <?php endif; ?>

                                        <?php if ($order['status'] === 'Completed'): ?>
                                            <a 
                                                class="btn secondary" 
                                                href="request-refund.php?id=<?php echo $orderIdInt; ?>"
                                            >
                                                <?php echo $latestRefundStatus === 'Rejected' ? 'Request Again' : 'Refund'; ?>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>

                                <td>
                                    <a 
                                        class="btn secondary receipt-btn" 
                                        href="receipt.php?order_id=<?php echo $orderIdInt; ?>"
                                    >
                                        View Receipt
                                    </a>
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
                                    <span class="status <?php echo strtolower(str_replace(' ', '-', trim($order['status']))); ?>">
                                        <?php echo htmlspecialchars($order['status']); ?>
                                    </span>
                                </div>
                            </div>

                            <div class="tracking-timeline">
                                <?php if (empty($trackingHistory)): ?>
                                    <div class="notice">
                                        No tracking history available.
                                    </div>
                                <?php else: ?>
                                    <?php foreach ($trackingHistory as $history): ?>
                                        <div class="timeline-step <?php echo strtolower(str_replace(' ', '-', trim($history['status']))); ?>">
                                            <div class="timeline-dot"></div>

                                            <div class="timeline-content">
                                                <strong><?php echo htmlspecialchars($history['status']); ?></strong>

                                                <p>
                                                    <?php echo date("d M Y, h:i A", strtotime($history['changed_at'])); ?>
                                                </p>

                                                <?php if (!empty($history['note'])): ?>
                                                    <span class="small">
                                                        <?php echo htmlspecialchars($history['note']); ?>
                                                    </span>
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
            <a href="order-history.php">Order History</a>
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
        const modal = button.closest('.modal-overlay');

        if (modal) {
            modal.classList.remove('show');
        }
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