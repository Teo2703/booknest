<?php
include '../app.php';
requireCustomer();

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$errors = [];
$success = '';

function getCartItemsForCheckout($conn) {
    $items = [];
    $subtotal = 0;

    if (empty($_SESSION['cart'])) {
        return [$items, $subtotal];
    }

    $bookIds = array_keys($_SESSION['cart']);
    $placeholders = implode(',', array_fill(0, count($bookIds), '?'));
    $types = str_repeat('i', count($bookIds));

    $stmt = $conn->prepare("SELECT book_id, title, author, category, price, stock FROM books WHERE book_id IN ($placeholders)");
    $stmt->bind_param($types, ...$bookIds);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($book = $result->fetch_assoc()) {
        $bookId = (int)$book['book_id'];
        $quantity = isset($_SESSION['cart'][$bookId]) ? (int)$_SESSION['cart'][$bookId] : 0;

        if ($quantity < 1) {
            continue;
        }

        $lineTotal = (float)$book['price'] * $quantity;
        $subtotal += $lineTotal;

        $items[] = [
            'book' => $book,
            'quantity' => $quantity,
            'line_total' => $lineTotal
        ];
    }

    return [$items, $subtotal];
}

function orderColumnExists($conn, $columnName) {
    $sql = "
        SELECT COUNT(*) AS total
        FROM INFORMATION_SCHEMA.COLUMNS 
        WHERE TABLE_SCHEMA = DATABASE() 
        AND TABLE_NAME = 'orders' 
        AND COLUMN_NAME = ?
    ";

    $stmt = $conn->prepare($sql);


    if (!$stmt) {
        die("Database prepare failed: " . $conn->error);
    }

    $stmt->bind_param("s", $columnName);
    $stmt->execute();
    
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    return (int)$row['total'] > 0;
}

list($cartItems, $subtotal) = getCartItemsForCheckout($conn);
$delivery = $subtotal >= 80 || $subtotal == 0 ? 0 : 5;
$total = $subtotal + $delivery;

$name = $_SESSION['user_name'] ?? '';
$email = $_SESSION['user_email'] ?? '';
$contact = '';
$address = '';
$payment_method = 'Cash on Delivery';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $contact = trim($_POST['contact'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $payment_method = trim($_POST['payment_method'] ?? 'Cash on Delivery');

    if ($name === '') {
        $errors[] = "Full name is required.";
    }

    if ($contact === '') {
        $errors[] = "Contact number is required.";
    }

    if ($email === '') {
        $errors[] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    }

    if ($address === '') {
        $errors[] = "Delivery address is required.";
    }

    if (!in_array($payment_method, ['Cash on Delivery', 'Online Transfer'])) {
        $errors[] = "Invalid payment method.";
    }

    list($cartItems, $subtotal) = getCartItemsForCheckout($conn);
    $delivery = $subtotal >= 80 || $subtotal == 0 ? 0 : 5;
    $total = $subtotal + $delivery;

    if (empty($cartItems)) {
        $errors[] = "Your cart is empty.";
    }

    foreach ($cartItems as $item) {
        $book = $item['book'];
        if ((int)$item['quantity'] > (int)$book['stock']) {
            $errors[] = htmlspecialchars($book['title']) . " does not have enough stock.";
        }
    }

    if (empty($errors)) {
        try {
            $conn->begin_transaction();

            $columns = ['user_id', 'total_amount', 'status'];
            $placeholders = ['?', '?', '?'];
            $types = 'ids';
            $values = [(int)$_SESSION['user_id'], (float)$total, 'Pending'];

            // These columns are included in the updated SQL file. The checks keep the page usable even if the old SQL was imported.
            $optionalColumns = [
                'delivery_name' => [$name, 's'],
                'delivery_contact' => [$contact, 's'],
                'delivery_email' => [$email, 's'],
                'delivery_address' => [$address, 's'],
                'payment_method' => [$payment_method, 's']
            ];

            foreach ($optionalColumns as $column => $data) {
                if (orderColumnExists($conn, $column)) {
                    $columns[] = $column;
                    $placeholders[] = '?';
                    $types .= $data[1];
                    $values[] = $data[0];
                }
            }

            $sql = "INSERT INTO orders (" . implode(',', $columns) . ") VALUES (" . implode(',', $placeholders) . ")";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param($types, ...$values);
            $stmt->execute();
            $order_id = $conn->insert_id;

            $itemStmt = $conn->prepare("INSERT INTO order_items (order_id, book_id, quantity, price) VALUES (?, ?, ?, ?)");
            $stockStmt = $conn->prepare("UPDATE books SET stock = stock - ? WHERE book_id = ? AND stock >= ?");

            foreach ($cartItems as $item) {
                $book = $item['book'];
                $book_id = (int)$book['book_id'];
                $quantity = (int)$item['quantity'];
                $price = (float)$book['price'];

                $itemStmt->bind_param("iiid", $order_id, $book_id, $quantity, $price);
                $itemStmt->execute();

                $stockStmt->bind_param("iii", $quantity, $book_id, $quantity);
                $stockStmt->execute();

                if ($stockStmt->affected_rows !== 1) {
                    throw new Exception("Stock update failed for " . $book['title']);
                }
            }

            $conn->commit();
            $_SESSION['cart'] = [];

            header("Location: order-history.php?placed=" . $order_id);
            exit();
        } catch (Exception $e) {
            $conn->rollback();
            $errors[] = "Order could not be placed. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout | BookNest</title>
    <link rel="stylesheet" href="../css/style.css?v=123">
</head>
<body>

<div class="topbar">
    <div class="container">
        <span>Mini Online Bookstore</span>
        <span>Free local delivery for orders above RM80</span>
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
            <a href="../auth/logout.php">👤 <?php echo htmlspecialchars($_SESSION['user_name']); ?> | Logout</a>
        </nav>
    </div>
</header>

<section class="page-title">
    <div class="container">
        <p class="eyebrow">Checkout</p>
        <h1>Confirm Your Order</h1>
        <p>Enter delivery details and confirm selected books.</p>
    </div>
</section>

<main class="section">
    <div class="container two-col">
        <form class="form-card" method="POST" action="checkout.php">
            <h2>Delivery Information</h2>

            <?php if (!empty($errors)): ?>
                <div class="notice" style="margin-bottom:1rem;">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if (empty($cartItems)): ?>
                <div class="notice" style="margin-bottom:1rem;">
                    Your cart is empty. Please add books before checkout.
                </div>
                <a class="btn" href="../books/books.php">Browse Books</a>
            <?php else: ?>
                <div class="form-grid">
                    <div class="field">
                        <label>Full Name</label>
                        <input class="input" name="name" value="<?php echo htmlspecialchars($name); ?>" placeholder="Enter full name">
                    </div>
                    <div class="field">
                        <label>Contact Number</label>
                        <input class="input" name="contact" value="<?php echo htmlspecialchars($contact); ?>" placeholder="Enter phone number">
                    </div>
                </div>

                <div class="field">
                    <label>Email</label>
                    <input class="input" type="email" name="email" value="<?php echo htmlspecialchars($email); ?>" placeholder="Enter email">
                </div>

                <div class="field">
                    <label>Delivery Address</label>
                    <textarea name="address" rows="4" placeholder="Enter delivery address"><?php echo htmlspecialchars($address); ?></textarea>
                </div>

                <div class="field">
                    <label>Payment Method</label>
                    <select name="payment_method">
                        <option <?php if ($payment_method === 'Cash on Delivery') echo 'selected'; ?>>Cash on Delivery</option>
                        <option <?php if ($payment_method === 'Online Transfer') echo 'selected'; ?>>Online Transfer</option>
                    </select>
                </div>

                <button class="btn" type="submit">Place Order</button>
                <a class="btn secondary" style="width:auto;margin-left:.5rem;" href="cart.php">Back to Cart</a>
            <?php endif; ?>
        </form>

        <aside class="summary">
            <h2>Order Summary</h2>

            <?php if (empty($cartItems)): ?>
                <p class="small">No items selected.</p>
            <?php else: ?>
                <?php foreach ($cartItems as $item): ?>
                    <div class="summary-row">
                        <span><?php echo htmlspecialchars($item['book']['title']); ?> x <?php echo (int)$item['quantity']; ?></span>
                        <strong>RM<?php echo number_format($item['line_total'], 2); ?></strong>
                    </div>
                <?php endforeach; ?>

                <div class="summary-row"><span>Subtotal</span><strong>RM<?php echo number_format($subtotal, 2); ?></strong></div>
                <div class="summary-row"><span>Delivery</span><strong>RM<?php echo number_format($delivery, 2); ?></strong></div>
                <div class="summary-row total"><span>Total</span><span>RM<?php echo number_format($total, 2); ?></span></div>
                <p class="notice" style="margin-top:1rem;">After checkout, the order and order item details will be stored in the database.</p>
            <?php endif; ?>
        </aside>
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
