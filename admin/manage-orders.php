<?php
include '../db.php';

$statusFilter = isset($_GET['status']) ? $_GET['status'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';

$sql = "
    SELECT orders.order_id, users.name, orders.order_date, orders.total_amount, orders.status
    FROM orders
    LEFT JOIN users ON orders.user_id = users.user_id
    WHERE 1
";

$params = [];
$types = "";

if ($search != '') {
    $sql .= " AND (orders.order_id LIKE ? OR users.name LIKE ?)";
    $keyword = "%$search%";
    $params[] = $keyword;
    $params[] = $keyword;
    $types .= "ss";
}

if ($statusFilter != '' && $statusFilter != 'All Status') {
    $sql .= " AND orders.status = ?";
    $params[] = $statusFilter;
    $types .= "s";
}

$sql .= " ORDER BY orders.order_date DESC";

$stmt = $conn->prepare($sql);
    
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$orders = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders | BookNest</title>

    <link rel="stylesheet" href="../css/style.css">
</head>

<body>

    <!-- Top Bar -->
    <div class="topbar">
        <div class="container">
            <span>Mini Online Bookstore</span>
            <span>Admin order management</span>
        </div>
    </div>

    <!-- Navigation Bar -->
    <header class="navbar">
        <div class="container nav-inner">
            <a class="brand" href="index.php">
                Book<span>Nest</span>
            </a>

            <nav class="nav-links">
                <a href="../index.php">Home</a>
                <a href="../books/books.php">Books</a>
                <a href="../group.html">Group</a>
                <a href="../login.php">Login</a>
            </nav>
        </div>
    </header>

    <!-- Page Title -->
    <section class="page-title">
        <div class="container">
            <p class="eyebrow">Admin Area</p>
            <h1>Manage Orders</h1>
            <p>Admin can review customer orders and update order status.</p>
        </div>
    </section>

    <!-- Main Section -->
    <main class="section">
        <div class="container admin-layout">

            <!-- Sidebar -->
            <aside class="sidebar">
                <a href="admin-dashboard.php">Dashboard</a>
                <a href="manage-books.php">Manage Books</a>
                <a class="active" href="manage-orders.php">Manage Orders</a>
                <a href="../index.php">Logout</a>
            </aside>

            <!-- Manage Orders Content -->
            <section>

                <!-- Search and Filter Area -->
                <form method="GET" class="filters" style="grid-template-columns:2fr 1fr auto">
                    <input class="input" name="search" placeholder="Search by order ID or customer" value="<?php echo htmlspecialchars($search); ?>">

                    <select name="status">
                        <option>All Status</option>
                        <option <?php if($statusFilter=="Pending") echo "selected"; ?>>Pending</option>
                        <option <?php if($statusFilter=="Processing") echo "selected"; ?>>Processing</option>
                        <option <?php if($statusFilter=="Completed") echo "selected"; ?>>Completed</option>
                        <option <?php if($statusFilter=="Cancelled") echo "selected"; ?>>Cancelled</option>
                    </select>

                    <button class="btn secondary" type="submit">Filter</button>
                </form>

                <!-- Orders Table -->
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Date</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Update</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php while ($order = $orders->fetch_assoc()): ?>
                            <tr>
                                <td>#BN<?php echo str_pad($order['order_id'], 4, '0', STR_PAD_LEFT); ?></td>
                                <td><?php echo htmlspecialchars($order['name']); ?></td>
                                <td><?php echo date("d M Y", strtotime($order['order_date'])); ?></td>
                                <td>RM<?php echo number_format($order['total_amount'], 2); ?></td>
                                <td>
                                    <span class="status processing">
                                        <?php echo htmlspecialchars($order['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <form method="POST" action="update-order-status.php">
                                        <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">

                                        <select name="status">
                                            <option <?php if($order['status']=="Pending") echo "selected"; ?>>Pending</option>
                                            <option <?php if($order['status']=="Processing") echo "selected"; ?>>Processing</option>
                                            <option <?php if($order['status']=="Completed") echo "selected"; ?>>Completed</option>
                                            <option <?php if($order['status']=="Cancelled") echo "selected"; ?>>Cancelled</option>
                                        </select>

                                        <button class="btn secondary update-btn" type="submit">Update</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

            </section>
        </div>
    </main>

    <!-- Footer -->
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
                <a href="../admin-dashboard.php">Dashboard</a>
                <a href="../manage-books.php">Manage Books</a>
                <a href="../manage-orders.php">Manage Orders</a>
            </div>

        </div>
    </footer>

</body>
</html>