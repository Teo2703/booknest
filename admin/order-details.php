<?php
include '../app.php';
requireAdmin();

$order_id = isset($_GET['id']) ? $_GET['id'] : 0;

/* ======================
ORDER INFO
====================== */
$stmt = $conn->prepare("
    SELECT orders.*, users.name 
    FROM orders
    LEFT JOIN users ON orders.user_id = users.user_id
    WHERE orders.order_id = ?
");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

/* ======================
ORDER ITEMS
====================== */
$stmt2 = $conn->prepare("
    SELECT order_items.*, books.title 
    FROM order_items
    LEFT JOIN books ON order_items.book_id = books.book_id
    WHERE order_items.order_id = ?
");
$stmt2->bind_param("i", $order_id);
$stmt2->execute();
$items = $stmt2->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Order Details</title>
    <link rel="stylesheet" href="../css/style.css?v=123">
</head>

<body>

<div class="container">

<h1>Order Details</h1>

<!-- ORDER SUMMARY -->
<div class="card1">
    <p><strong>Order ID:</strong> #BN<?php echo str_pad($order['order_id'],4,'0',STR_PAD_LEFT); ?></p>
    <p><strong>Customer:</strong> <?php echo htmlspecialchars($order['name']); ?></p>
    <p><strong>Date:</strong> <?php echo $order['order_date']; ?></p>
    <p><strong>Total:</strong> RM<?php echo number_format($order['total_amount'],2); ?></p>
</div>

<?php
$steps = ["Pending", "Processing", "Completed"];
$currentIndex = array_search($order['status'], $steps);
?>

<div class="timeline">

    <div class="timeline-line"></div>

    <?php foreach ($steps as $index => $step): ?>
        <div class="timeline-item">

            <div class="circle 
                <?php 
                    if ($orderStatus == "Completed") {
                        echo 'done';
                    } else {
                        if ($index < $currentIndex) echo 'done';
                        elseif ($index == $currentIndex) echo 'active';
                    }
                ?>">
            </div>

            <p class="label"><?php echo $step; ?></p>

        </div>
    <?php endforeach; ?>

</div>
<!-- ORDER ITEMS -->
<div class="table-wrap">
<table>
<thead>
<tr>
<th>Book</th>
<th>Price</th>
<th>Quantity</th>
<th>Subtotal</th>
</tr>
</thead>

<tbody>
<?php while ($row = $items->fetch_assoc()): ?>
<tr>
<td><?php echo htmlspecialchars($row['title']); ?></td>
<td>RM<?php echo number_format($row['price'],2); ?></td>
<td><?php echo $row['quantity']; ?></td>
<td>RM<?php echo number_format($row['price'] * $row['quantity'],2); ?></td>
</tr>
<?php endwhile; ?>
</tbody>

</table>
</div>

<a href="manage-orders.php" class="btn back-btn" style="margin-top:30px;">⬅ Back</a>
</div>

</body>
</html>