<?php
include '../app.php';
requireAdmin();

$message = '';
$error = '';

// Approve or Reject a refund
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $refund_id = (int)$_POST['refund_id'];
    $action = $_POST['action'];

    $stmt = $conn->prepare("SELECT * FROM refunds WHERE refund_id = ?");
    $stmt->bind_param("i", $refund_id);
    $stmt->execute();
    $refund = $stmt->get_result()->fetch_assoc();

    if (!$refund) {
        $error = "Refund request not found.";
    } elseif ($refund['status'] !== 'Pending') {
        $error = "This refund has already been resolved.";
    } else {
        try {
            $conn->begin_transaction();

            if ($action === 'approve') {
                $updateRefund = $conn->prepare("UPDATE refunds SET status = 'Approved', resolved_at = NOW() WHERE refund_id = ?");
                $updateRefund->bind_param("i", $refund_id);
                $updateRefund->execute();

                $updateOrder = $conn->prepare("UPDATE orders SET status = 'Refunded' WHERE order_id = ?");
                $updateOrder->bind_param("i", $refund['order_id']);
                $updateOrder->execute();

                $historyStmt = $conn->prepare("
                    INSERT INTO order_status_history (order_id, status, changed_by_user_id, note)
                    VALUES (?, 'Refund Approved', ?, 'Refund approved by admin')
                ");
                $historyStmt->bind_param("ii", $refund['order_id'], $_SESSION['user_id']);
                $historyStmt->execute();

                $message = "Refund approved and order marked as refunded.";

            } elseif ($action === 'reject') {
                $updateRefund = $conn->prepare("UPDATE refunds SET status = 'Rejected', resolved_at = NOW() WHERE refund_id = ?");
                $updateRefund->bind_param("i", $refund_id);
                $updateRefund->execute();

                $historyStmt = $conn->prepare("
                    INSERT INTO order_status_history (order_id, status, changed_by_user_id, note)
                    VALUES (?, 'Refund Rejected', ?, 'Refund rejected by admin')
                ");
                $historyStmt->bind_param("ii", $refund['order_id'], $_SESSION['user_id']);
                $historyStmt->execute();

                $message = "Refund request rejected.";
            }

            $conn->commit();

        } catch (Exception $e) {
            $conn->rollback();
            $error = "Something went wrong. Please try again.";
        }
    }
}

// Fetch all refund requests
$refunds = $conn->query("
    SELECT 
        refunds.refund_id,
        refunds.order_id,
        refunds.reason,
        refunds.amount,
        refunds.status,
        refunds.requested_at,
        refunds.resolved_at,
        users.name AS customer_name,
        users.email AS customer_email
    FROM refunds
    JOIN orders ON refunds.order_id = orders.order_id
    JOIN users ON orders.user_id = users.user_id
    ORDER BY 
        CASE refunds.status WHEN 'Pending' THEN 0 ELSE 1 END,
        refunds.requested_at DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Refunds | BookNest</title>
    <link rel="stylesheet" href="../css/style.css?v=123">
</head>
<body>

<div class="topbar">
    <div class="container">
        <span>Mini Online Bookstore</span>
        <span>Admin refund management</span>
    </div>
</div>

<?php include __DIR__ . '/../includes/navigation.php'; ?>

<section class="page-title">
    <div class="container">
        <p class="eyebrow">Admin Area</p>
        <h1>Manage Refunds</h1>
        <p>Review and resolve customer refund requests.</p>
    </div>
</section>

<main class="section">
    <div class="container admin-layout">

        <aside class="sidebar">
            <a href="admin-dashboard.php">Dashboard</a>
            <a href="manage-books.php">Manage Books</a>
            <a href="manage-orders.php">Manage Orders</a>
            <a class="active" href="manage-refunds.php">Manage Refunds</a>
            <a href="../auth/logout.php">Logout</a>
        </aside>

        <section>

            <?php if ($message !== ''): ?>
                <div class="notice" style="margin-bottom:1rem;"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>

            <?php if ($error !== ''): ?>
                <div class="notice" style="margin-bottom:1rem;"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Refund ID</th>
                            <th>Order</th>
                            <th>Customer</th>
                            <th>Reason</th>
                            <th>Amount</th>
                            <th>Requested</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($refunds->num_rows === 0): ?>
                            <tr><td colspan="8">No refund requests yet.</td></tr>
                        <?php endif; ?>

                        <?php while ($r = $refunds->fetch_assoc()): ?>
                        <tr>
                            <td>#<?php echo $r['refund_id']; ?></td>
                            <td>#BN<?php echo str_pad($r['order_id'], 4, '0', STR_PAD_LEFT); ?></td>
                            <td>
                                <?php echo htmlspecialchars($r['customer_name']); ?><br>
                                <span class="small"><?php echo htmlspecialchars($r['customer_email']); ?></span>
                            </td>
                            <td><?php echo htmlspecialchars($r['reason']); ?></td>
                            <td>RM<?php echo number_format($r['amount'], 2); ?></td>
                            <td><?php echo date("d M Y", strtotime($r['requested_at'])); ?></td>
                            <td>
                                <span class="status <?php echo strtolower($r['status']); ?>">
                                    <?php echo htmlspecialchars($r['status']); ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($r['status'] === 'Pending'): ?>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="refund_id" value="<?php echo $r['refund_id']; ?>">
                                        <input type="hidden" name="action" value="approve">
                                        <button class="btn secondary" type="submit" onclick="return confirm('Approve this refund?')">Approve</button>
                                    </form>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="refund_id" value="<?php echo $r['refund_id']; ?>">
                                        <input type="hidden" name="action" value="reject">
                                        <button class="btn danger" type="submit" onclick="return confirm('Reject this refund?')">Reject</button>
                                    </form>
                                <?php else: ?>
                                    <span class="small">Resolved <?php echo date("d M Y", strtotime($r['resolved_at'])); ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

        </section>
    </div>
</main>

<footer class="footer">
    <div class="container footer-grid">
        <div>
            <h3>BookNest</h3>
            <p>A clean static prototype for the Mini Online Bookstore e-commerce system.</p>
        </div>
        <div>
            <h4>Customer</h4>
            <a href="../books/books.php">Browse Books</a>
            <a href="../orders/cart.php">Shopping Cart</a>
            <a href="../orders/checkout.php">Checkout</a>
        </div>
        <div>
            <h4>Admin</h4>
            <a href="admin-dashboard.php">Dashboard</a>
            <a href="manage-books.php">Manage Books</a>
            <a href="manage-orders.php">Manage Orders</a>
        </div>
    </div>
</footer>

</body>
</html>