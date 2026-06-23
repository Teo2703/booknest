<?php
include '../app.php';
requireCustomer();

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$errors = [];

function getCartItemsForCheckout($conn) {
    $items = [];
    $subtotal = 0;

    if (empty($_SESSION['cart'])) {
        return [$items, $subtotal];
    }

    $bookIds = array_keys($_SESSION['cart']);
    $placeholders = implode(',', array_fill(0, count($bookIds), '?'));
    $types = str_repeat('i', count($bookIds));

    $stmt = $conn->prepare("
        SELECT book_id, title, author, category, price, stock 
        FROM books 
        WHERE book_id IN ($placeholders)
    ");

    if (!$stmt) {
        die("Database prepare failed: " . $conn->error);
    }

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

function createOrder($conn, $cartItems, $total, $name, $contact, $email, $address, $payment_method, $payment_status = 'Pending', $payment_reference = null, $card_last_four = null) {
    $user_id = (int)$_SESSION['user_id'];

    $conn->begin_transaction();

    try {
        $paid_at = null;

        if ($payment_status === 'Paid') {
            $paid_at = date('Y-m-d H:i:s');
        }

        $stmt = $conn->prepare("
            INSERT INTO orders 
            (
                user_id, 
                total_amount, 
                status, 
                delivery_name, 
                delivery_contact, 
                delivery_email, 
                delivery_address, 
                payment_method, 
                payment_status, 
                payment_reference, 
                card_last_four, 
                paid_at
            ) 
            VALUES 
            (?, ?, 'Pending', ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        if (!$stmt) {
            throw new Exception("Order insert prepare failed: " . $conn->error);
        }

        $stmt->bind_param(
            "idsssssssss",
            $user_id,
            $total,
            $name,
            $contact,
            $email,
            $address,
            $payment_method,
            $payment_status,
            $payment_reference,
            $card_last_four,
            $paid_at
        );

        $stmt->execute();
        $order_id = $conn->insert_id;

        $itemStmt = $conn->prepare("
            INSERT INTO order_items 
            (order_id, book_id, quantity, price) 
            VALUES (?, ?, ?, ?)
        ");

        if (!$itemStmt) {
            throw new Exception("Order item insert prepare failed: " . $conn->error);
        }

        $stockStmt = $conn->prepare("
            UPDATE books 
            SET stock = stock - ? 
            WHERE book_id = ? AND stock >= ?
        ");

        if (!$stockStmt) {
            throw new Exception("Stock update prepare failed: " . $conn->error);
        }

        foreach ($cartItems as $item) {
            $book_id = (int)$item['book']['book_id'];
            $quantity = (int)$item['quantity'];
            $price = (float)$item['book']['price'];

            if ($quantity > (int)$item['book']['stock']) {
                throw new Exception("Not enough stock for " . $item['book']['title']);
            }

            $itemStmt->bind_param("iiid", $order_id, $book_id, $quantity, $price);
            $itemStmt->execute();

            $stockStmt->bind_param("iii", $quantity, $book_id, $quantity);
            $stockStmt->execute();

            if ($stockStmt->affected_rows === 0) {
                throw new Exception("Stock update failed for " . $item['book']['title']);
            }
        }

        $conn->commit();

        $_SESSION['cart'] = [];
        unset($_SESSION['checkout_data']);

        return $order_id;

    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    }
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

    if (empty($cartItems)) {
        $errors[] = "Your cart is empty.";
    }

    $allowedPayments = ['Cash on Delivery', 'Online Transfer', 'Credit/Debit Card'];

    if (!in_array($payment_method, $allowedPayments)) {
        $errors[] = "Invalid payment method selected.";
    }

    foreach ($cartItems as $item) {
        if ((int)$item['quantity'] > (int)$item['book']['stock']) {
            $errors[] = $item['book']['title'] . " does not have enough stock.";
        }
    }

    if (empty($errors)) {
        if ($payment_method === 'Cash on Delivery') {
            try {
                $order_id = createOrder(
                    $conn,
                    $cartItems,
                    $total,
                    $name,
                    $contact,
                    $email,
                    $address,
                    $payment_method,
                    'Pending',
                    null,
                    null
                );

                header("Location: receipt.php?order_id=" . $order_id);
                exit();

            } catch (Exception $e) {
                $errors[] = "Order could not be placed. " . $e->getMessage();
            }
        } else {
            $_SESSION['checkout_data'] = [
                'name' => $name,
                'contact' => $contact,
                'email' => $email,
                'address' => $address,
                'payment_method' => $payment_method,
                'subtotal' => $subtotal,
                'delivery' => $delivery,
                'total' => $total
            ];

            header("Location: payment.php");
            exit();
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
    <link rel="stylesheet" href="../css/style.css?v=140">
</head>

<body>

<div class="topbar">
    <div class="container">
        <span>Mini Online Bookstore</span>
        <span>Free local delivery for orders above RM80</span>
    </div>
</div>

<?php include __DIR__ . '/../includes/navigation.php'; ?>

<section class="page-title">
    <div class="container">
        <p class="eyebrow">Checkout</p>
        <h1>Complete Your Order</h1>
        <p>Enter your delivery details and choose your payment method.</p>
    </div>
</section>

<main class="section">
    <div class="container two-col">

        <div>
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
                    <div class="notice">
                        Your cart is empty. Please add books before checkout.
                    </div>

                    <a class="btn" href="../books/books.php">
                        Browse Books
                    </a>
                <?php else: ?>

                    <div class="field">
                        <label>Full Name</label>
                        <input 
                            class="input" 
                            type="text" 
                            name="name" 
                            value="<?php echo htmlspecialchars($name); ?>" 
                            required
                        >
                    </div>

                    <div class="field">
                        <label>Contact Number</label>
                        <input 
                            class="input" 
                            type="text" 
                            name="contact" 
                            value="<?php echo htmlspecialchars($contact); ?>" 
                            required
                        >
                    </div>

                    <div class="field">
                        <label>Email</label>
                        <input 
                            class="input" 
                            type="email" 
                            name="email" 
                            value="<?php echo htmlspecialchars($email); ?>" 
                            required
                        >
                    </div>

                    <div class="field">
                        <label>Delivery Address</label>
                        <textarea name="address" rows="4" required><?php echo htmlspecialchars($address); ?></textarea>
                    </div>

                    <div class="field">
                        <label>Payment Method</label>
                        <select name="payment_method" required>
                            <option value="Cash on Delivery" <?php echo $payment_method === 'Cash on Delivery' ? 'selected' : ''; ?>>
                                Cash on Delivery
                            </option>

                            <option value="Online Transfer" <?php echo $payment_method === 'Online Transfer' ? 'selected' : ''; ?>>
                                Online Transfer
                            </option>

                            <option value="Credit/Debit Card" <?php echo $payment_method === 'Credit/Debit Card' ? 'selected' : ''; ?>>
                                Credit/Debit Card
                            </option>
                        </select>
                    </div>

                    <div class="notice" style="margin-bottom:1rem;">
                        <strong>Payment Note:</strong><br>
                        Cash on Delivery will place the order directly. Online Transfer and Credit/Debit Card will continue to the payment page.
                    </div>

                    <button class="btn" type="submit">
                        Continue
                    </button>

                    <a class="btn secondary" href="cart.php" style="margin-top:0.75rem;">
                        Back to Cart
                    </a>

                <?php endif; ?>
            </form>
        </div>

        <aside class="summary">
            <h2>Order Summary</h2>

            <?php if (!empty($cartItems)): ?>
                <?php foreach ($cartItems as $item): ?>
                    <div class="summary-row">
                        <span>
                            <?php echo htmlspecialchars($item['book']['title']); ?>
                            x <?php echo (int)$item['quantity']; ?>
                        </span>
                        <strong>
                            RM<?php echo number_format($item['line_total'], 2); ?>
                        </strong>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <div class="summary-row">
                <span>Subtotal</span>
                <strong>RM<?php echo number_format($subtotal, 2); ?></strong>
            </div>

            <div class="summary-row">
                <span>Delivery</span>
                <strong>RM<?php echo number_format($delivery, 2); ?></strong>
            </div>

            <div class="summary-row total">
                <span>Total</span>
                <span>RM<?php echo number_format($total, 2); ?></span>
            </div>
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