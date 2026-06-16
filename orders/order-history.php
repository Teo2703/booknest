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
            <?php if ($orders->num_rows === 0): ?>
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
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($order = $orders->fetch_assoc()): ?>
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
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
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

</body>
</html>
