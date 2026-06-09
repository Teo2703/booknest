<?php
include '../app.php';
requireAdmin();

$totalBooks = $conn->query("SELECT COUNT(*) AS total FROM books")->fetch_assoc()['total'];
$totalOrders = $conn->query("SELECT COUNT(*) AS total FROM orders")->fetch_assoc()['total'];
$pendingOrders = $conn->query("SELECT COUNT(*) AS total FROM orders WHERE status='Pending'")->fetch_assoc()['total'];
$totalCustomers = $conn->query("SELECT COUNT(*) AS total FROM users WHERE role='customer'")->fetch_assoc()['total'];

$recentOrders = $conn->query("
    SELECT orders.order_id, users.name, orders.total_amount, orders.status
    FROM orders
    LEFT JOIN users ON orders.user_id = users.user_id
    ORDER BY orders.order_date DESC
    LIMIT 5
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | BookNest</title>

    <link rel="stylesheet" href="../css/style.css">
</head>

<body>

    <!-- Top Bar -->
    <div class="topbar">
        <div class="container">
            <span>Mini Online Bookstore</span>
            <span>Admin management area</span>
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
            </nav>
        </div>
    </header>

    <!-- Page Title -->
    <section class="page-title">
        <div class="container">
            <p class="eyebrow">Admin Area</p>
            <h1>Admin Dashboard</h1>
            <p>Overview of bookstore records and management shortcuts.</p>
        </div>
    </section>

    <!-- Main Admin Section -->
    <main class="section">
        <div class="container admin-layout">

            <!-- Sidebar -->
            <aside class="sidebar">
                <a class="active" href="admin-dashboard.php">Dashboard</a>
                <a href="manage-books.php">Manage Books</a>
                <a href="manage-orders.php">Manage Orders</a>
                <a href="index.php">Logout</a>
            </aside>

            <!-- Dashboard Content -->
            <section>

                <!-- Statistics Cards -->
                <div class="stat-grid">

                    <div class="stat"><span>Total Books</span><br><strong><?php echo $totalBooks; ?></strong></div>
                    <div class="stat"><span>Total Orders</span><br><strong><?php echo $totalOrders; ?></strong></div>
                    <div class="stat"><span>Pending Orders</span><br><strong><?php echo $pendingOrders; ?></strong></div>
                    <div class="stat"><span>Customers</span><br><strong><?php echo $totalCustomers; ?></strong></div>

                </div>

                <!-- Recent Orders Header -->
                <div class="section-head" style="margin-top: 2rem;">
                    <h2>Recent Orders</h2>
                    <a class="btn secondary" href="manage-orders.php">View Orders</a>
                </div>

                <!-- Recent Orders Table -->
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Order</th>
                                <th>Customer</th>
                                <th>Total</th>
                                <th>Status</th>
                            </tr>
                        </thead>

                    <tbody>
                        <?php while ($row = $recentOrders->fetch_assoc()): ?>
                        <tr>
                            <td>#BN<?php echo str_pad($row['order_id'], 4, '0', STR_PAD_LEFT); ?></td>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td>RM<?php echo number_format($row['total_amount'], 2); ?></td>
                            <td>
                                <span class="status processing">
                                    <?php echo htmlspecialchars($row['status']); ?>
                                </span>
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
                <a href="../books.php">Browse Books</a>
                <a href="../cart.php">Shopping Cart</a>
                <a href="../checkout.php">Checkout</a>
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