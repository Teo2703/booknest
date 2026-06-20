<?php
include '../app.php';
requireCustomer();

$user_id = (int)$_SESSION['user_id'];
$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$error = '';

// Check the order belongs to this customer and is eligible for refund
$stmt = $conn->prepare("SELECT order_id, status, total_amount FROM orders WHERE order_id = ? AND user_id = ?");
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    header("Location: order-history.php");
    exit();
}

if ($order['status'] !== 'Completed') {
    header("Location: order-history.php?refund_error=not_eligible");
    exit();
}

// Check if a refund was already requested for this order
$checkStmt = $conn->prepare("SELECT refund_id FROM refunds WHERE order_id = ?");
$checkStmt->bind_param("i", $order_id);
$checkStmt->execute();
$existing = $checkStmt->get_result()->fetch_assoc();

if ($existing) {
    header("Location: order-history.php?refund_error=already_requested");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reason = trim($_POST['reason'] ?? '');

    if ($reason === '') {
        $error = "Please provide a reason for the refund.";
    } else {
        $stmt = $conn->prepare("
            INSERT INTO refunds (order_id, user_id, reason, amount, status)
            VALUES (?, ?, ?, ?, 'Pending')
        ");
        $stmt->bind_param("iisd", $order_id, $user_id, $reason, $order['total_amount']);
        $stmt->execute();

        header("Location: order-history.php?refund_requested=" . $order_id);
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Refund | BookNest</title>
    <link rel="stylesheet" href="../css/style.css?v=123">
</head>
<body>

<div class="topbar">
    <div class="container">
        <span>Mini Online Bookstore</span>
        <span>Refund request</span>
    </div>
</div>

<?php include __DIR__ . '/../includes/navigation.php'; ?>

<section class="page-title">
    <div class="container">
        <p class="eyebrow">Order #BN<?php echo str_pad($order_id, 4, '0', STR_PAD_LEFT); ?></p>
        <h1>Request a Refund</h1>
        <p>Tell us why you'd like a refund for this order. Our team will review your request.</p>
    </div>
</section>

<main class="section">
    <div class="container" style="max-width:600px;">
        <form class="form-card" method="POST">

            <?php if ($error !== ''): ?>
                <div class="notice" style="margin-bottom:1rem;">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <div class="field">
                <label>Order Total</label>
                <input class="input" value="RM<?php echo number_format($order['total_amount'], 2); ?>" disabled>
            </div>

            <div class="field">
                <label>Reason for Refund</label>
                <textarea name="reason" rows="5" placeholder="Explain why you're requesting a refund" required><?php echo isset($_POST['reason']) ? htmlspecialchars($_POST['reason']) : ''; ?></textarea>
            </div>

            <button class="btn" type="submit">Submit Refund Request</button>
            <a class="btn secondary" style="width:auto;margin-left:.5rem;" href="order-history.php">Cancel</a>

        </form>
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

</body>
</html>