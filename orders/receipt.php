<?php
include '../app.php';
requireCustomer();

if (!isset($_GET['order_id'])) {
    header("Location: order-history.php");
    exit();
}

$order_id = (int)$_GET['order_id'];
$user_id = (int)$_SESSION['user_id'];

$stmt = $conn->prepare("
    SELECT 
        orders.order_id,
        orders.user_id,
        orders.total_amount,
        orders.status,
        orders.order_date,
        orders.delivery_name,
        orders.delivery_contact,
        orders.delivery_email,
        orders.delivery_address,
        orders.payment_method,
        orders.payment_status,
        orders.payment_reference,
        orders.card_last_four,
        orders.paid_at
    FROM orders
    WHERE orders.order_id = ?
    AND orders.user_id = ?
");

if (!$stmt) {
    die("Database prepare failed: " . $conn->error);
}

$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    header("Location: order-history.php");
    exit();
}

$itemStmt = $conn->prepare("
    SELECT 
        order_items.quantity,
        order_items.price,
        books.title,
        books.author,
        books.category
    FROM order_items
    INNER JOIN books ON order_items.book_id = books.book_id
    WHERE order_items.order_id = ?
");

if (!$itemStmt) {
    die("Database prepare failed: " . $conn->error);
}

$itemStmt->bind_param("i", $order_id);
$itemStmt->execute();
$items = $itemStmt->get_result();

$itemsArray = [];
$subtotal = 0;

while ($item = $items->fetch_assoc()) {
    $lineTotal = (float)$item['price'] * (int)$item['quantity'];
    $subtotal += $lineTotal;

    $itemsArray[] = [
        'title' => $item['title'],
        'author' => $item['author'],
        'category' => $item['category'],
        'quantity' => (int)$item['quantity'],
        'price' => (float)$item['price'],
        'line_total' => $lineTotal
    ];
}

$total = (float)$order['total_amount'];
$delivery = $total - $subtotal;

if ($delivery < 0) {
    $delivery = 0;
}

$receiptNo = "BN" . str_pad($order['order_id'], 5, "0", STR_PAD_LEFT);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt | BookNest</title>
    <link rel="stylesheet" href="../css/style.css?v=150">

    <style>
        .receipt-card {
            max-width: 900px;
            margin: 0 auto;
            background: #fffdf8;
            border: 1px solid #e7d8c8;
            border-radius: 24px;
            padding: 2rem;
        }

        .receipt-header {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            border-bottom: 1px solid #e7d8c8;
            padding-bottom: 1rem;
            margin-bottom: 1.5rem;
        }

        .receipt-header h2 {
            margin: 0;
            color: #5b321c;
        }

        .receipt-no {
            text-align: right;
            color: #5b321c;
            font-weight: 700;
        }

        .receipt-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .receipt-box {
            background: #fff8ef;
            border: 1px solid #e7d8c8;
            border-radius: 18px;
            padding: 1rem;
        }

        .receipt-box h3 {
            margin-top: 0;
            color: #5b321c;
        }

        .receipt-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }

        .receipt-table th,
        .receipt-table td {
            border-bottom: 1px solid #e7d8c8;
            padding: 0.9rem;
            text-align: left;
        }

        .receipt-table th {
            background: #fff8ef;
            color: #5b321c;
        }

        .receipt-summary {
            max-width: 360px;
            margin-left: auto;
            margin-top: 1.5rem;
        }

        .receipt-summary-row {
            display: flex;
            justify-content: space-between;
            padding: 0.55rem 0;
            border-bottom: 1px solid #e7d8c8;
        }

        .receipt-summary-row.total {
            font-size: 1.2rem;
            font-weight: 800;
            color: #5b321c;
        }

        .receipt-actions {
            display: flex;
            justify-content: flex-end;
            gap: 0.75rem;
            margin-top: 1.5rem;
            flex-wrap: wrap;
        }

        .status-pill {
            display: inline-block;
            padding: 0.35rem 0.75rem;
            border-radius: 999px;
            background: #f0e4d7;
            color: #5b321c;
            font-weight: 700;
            font-size: 0.9rem;
        }

        @media print {
            .topbar,
            .navbar,
            .page-title,
            .footer,
            .receipt-actions {
                display: none !important;
            }

            body {
                background: white;
            }

            .receipt-card {
                border: none;
                padding: 0;
            }
        }

        @media (max-width: 768px) {
            .receipt-header,
            .receipt-grid {
                grid-template-columns: 1fr;
                display: grid;
            }

            .receipt-no {
                text-align: left;
            }

            .receipt-table {
                font-size: 0.9rem;
            }
        }
    </style>
</head>

<body>

<div class="topbar">
    <div class="container">
        <span>Mini Online Bookstore</span>
        <span>Receipt generated after successful order placement</span>
    </div>
</div>

<header class="navbar">
    <div class="container nav-inner">
        <a class="brand" href="../index.php">Book<span>Nest</span></a>

        <nav class="nav-links">
            <a href="../index.php">Home</a>
            <a href="../books/books.php">Books</a>
            <a href="cart.php">Cart</a>
            <a href="order-history.php">Orders</a>
            <a href="../auth/logout.php">
                👤 <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Customer'); ?> | Logout
            </a>
        </nav>
    </div>
</header>

<section class="page-title">
    <div class="container">
        <p class="eyebrow">Receipt</p>
        <h1>Order Receipt</h1>
        <p>Your order has been recorded successfully.</p>
    </div>
</section>

<main class="section">
    <div class="container">

        <div class="receipt-card">

            <div class="receipt-header">
                <div>
                    <h2>BookNest Receipt</h2>
                    <p>Mini Online Bookstore E-Commerce System</p>
                </div>

                <div class="receipt-no">
                    Receipt No: <?php echo htmlspecialchars($receiptNo); ?><br>
                    Date: <?php echo date("d M Y, h:i A", strtotime($order['order_date'])); ?>
                </div>
            </div>

            <div class="receipt-grid">

                <div class="receipt-box">
                    <h3>Customer / Delivery Details</h3>
                    <p>
                        <strong>Name:</strong>
                        <?php echo htmlspecialchars($order['delivery_name']); ?><br>

                        <strong>Contact:</strong>
                        <?php echo htmlspecialchars($order['delivery_contact']); ?><br>

                        <strong>Email:</strong>
                        <?php echo htmlspecialchars($order['delivery_email']); ?><br>

                        <strong>Address:</strong><br>
                        <?php echo nl2br(htmlspecialchars($order['delivery_address'])); ?>
                    </p>
                </div>

                <div class="receipt-box">
                    <h3>Payment Details</h3>
                    <p>
                        <strong>Payment Method:</strong>
                        <?php echo htmlspecialchars($order['payment_method']); ?><br>

                        <strong>Payment Status:</strong>
                        <span class="status-pill">
                            <?php echo htmlspecialchars($order['payment_status'] ?? 'Pending'); ?>
                        </span><br><br>

                        <?php if (!empty($order['payment_reference'])): ?>
                            <strong>Payment Reference:</strong>
                            <?php echo htmlspecialchars($order['payment_reference']); ?><br>
                        <?php endif; ?>

                        <?php if (!empty($order['card_last_four'])): ?>
                            <strong>Card:</strong>
                            **** **** **** <?php echo htmlspecialchars($order['card_last_four']); ?><br>
                        <?php endif; ?>

                        <?php if (!empty($order['paid_at'])): ?>
                            <strong>Paid At:</strong>
                            <?php echo date("d M Y, h:i A", strtotime($order['paid_at'])); ?><br>
                        <?php endif; ?>

                        <strong>Order Status:</strong>
                        <?php echo htmlspecialchars($order['status']); ?>
                    </p>
                </div>

            </div>

            <h3>Purchased Items</h3>

            <table class="receipt-table">
                <thead>
                    <tr>
                        <th>Book</th>
                        <th>Category</th>
                        <th>Qty</th>
                        <th>Price</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($itemsArray as $item): ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($item['title']); ?></strong><br>
                                <span class="small">
                                    <?php echo htmlspecialchars($item['author']); ?>
                                </span>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($item['category']); ?>
                            </td>

                            <td>
                                <?php echo (int)$item['quantity']; ?>
                            </td>

                            <td>
                                RM<?php echo number_format($item['price'], 2); ?>
                            </td>

                            <td>
                                RM<?php echo number_format($item['line_total'], 2); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="receipt-summary">
                <div class="receipt-summary-row">
                    <span>Subtotal</span>
                    <strong>RM<?php echo number_format($subtotal, 2); ?></strong>
                </div>

                <div class="receipt-summary-row">
                    <span>Delivery</span>
                    <strong>RM<?php echo number_format($delivery, 2); ?></strong>
                </div>

                <div class="receipt-summary-row total">
                    <span>Total</span>
                    <span>RM<?php echo number_format($total, 2); ?></span>
                </div>
            </div>

            <div class="receipt-actions">
                <button class="btn secondary" onclick="window.print()">
                    Print Receipt
                </button>

                <a class="btn secondary" href="order-history.php">
                    View Order History
                </a>

                <a class="btn" href="../books/books.php">
                    Continue Shopping
                </a>
            </div>

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

</body>
</html>