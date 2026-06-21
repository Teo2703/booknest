<?php
include '../app.php';
requireCustomer();

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if (empty($_SESSION['checkout_data']) || empty($_SESSION['cart'])) {
    header("Location: cart.php");
    exit();
}

$checkout = $_SESSION['checkout_data'];
$payment_method = $checkout['payment_method'] ?? '';
$errors = [];

function getCartItemsForPayment($conn) {
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

function createPaidOrder($conn, $cartItems, $total, $name, $contact, $email, $address, $payment_method, $payment_status, $payment_reference = null, $card_last_four = null) {
    $user_id = (int)$_SESSION['user_id'];

    $conn->begin_transaction();

    try {
        $paid_at = null;

        if ($payment_status === 'Paid' || $payment_status === 'Pending Verification') {
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

list($cartItems, $subtotal) = getCartItemsForPayment($conn);

$delivery = $subtotal >= 80 || $subtotal == 0 ? 0 : 5;
$total = $subtotal + $delivery;

$payment_reference = '';
$card_name = '';
$card_number = '';
$expiry = '';
$cvv = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($cartItems)) {
        $errors[] = "Your cart is empty.";
    }

    foreach ($cartItems as $item) {
        if ((int)$item['quantity'] > (int)$item['book']['stock']) {
            $errors[] = $item['book']['title'] . " does not have enough stock.";
        }
    }

    if ($payment_method === 'Online Transfer') {
        $payment_reference = trim($_POST['payment_reference'] ?? '');

        if ($payment_reference === '') {
            $errors[] = "Payment reference number is required.";
        }

        if (empty($errors)) {
            try {
                $order_id = createPaidOrder(
                    $conn,
                    $cartItems,
                    $total,
                    $checkout['name'],
                    $checkout['contact'],
                    $checkout['email'],
                    $checkout['address'],
                    'Online Transfer',
                    'Pending Verification',
                    $payment_reference,
                    null
                );

                header("Location: receipt.php?order_id=" . $order_id);
                exit();

            } catch (Exception $e) {
                $errors[] = "Payment could not be processed. " . $e->getMessage();
            }
        }
    }

    if ($payment_method === 'Credit/Debit Card') {
        $card_name = trim($_POST['card_name'] ?? '');
        $card_number = preg_replace('/\D/', '', $_POST['card_number'] ?? '');
        $expiry = trim($_POST['expiry'] ?? '');
        $cvv = preg_replace('/\D/', '', $_POST['cvv'] ?? '');

        if ($card_name === '') {
            $errors[] = "Cardholder name is required.";
        }

        if ($card_number === '') {
            $errors[] = "Card number is required.";
        } elseif (strlen($card_number) < 13 || strlen($card_number) > 19) {
            $errors[] = "Card number must be between 13 and 19 digits.";
        }

        if ($expiry === '') {
            $errors[] = "Expiry date is required.";
        }

        if ($cvv === '') {
            $errors[] = "CVV is required.";
        } elseif (strlen($cvv) < 3 || strlen($cvv) > 4) {
            $errors[] = "CVV must be 3 or 4 digits.";
        }

        $card_last_four = null;

        if ($card_number !== '') {
            $card_last_four = substr($card_number, -4);
        }

        if (empty($errors)) {
            try {
                $order_id = createPaidOrder(
                    $conn,
                    $cartItems,
                    $total,
                    $checkout['name'],
                    $checkout['contact'],
                    $checkout['email'],
                    $checkout['address'],
                    'Credit/Debit Card',
                    'Paid',
                    null,
                    $card_last_four
                );

                header("Location: receipt.php?order_id=" . $order_id);
                exit();

            } catch (Exception $e) {
                $errors[] = "Payment could not be processed. " . $e->getMessage();
            }
        }
    }
}

if ($payment_method !== 'Online Transfer' && $payment_method !== 'Credit/Debit Card') {
    header("Location: checkout.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment | BookNest</title>
    <link rel="stylesheet" href="../css/style.css?v=141">

    <style>
        .payment-method-box {
            background: #fffdf8;
            border: 1px solid #e7d8c8;
            border-radius: 22px;
            padding: 1.5rem;
            margin-bottom: 1rem;
        }

        .payment-method-box h3 {
            margin-top: 0;
            color: #5b321c;
        }

        .bank-box {
            background: #fff8ef;
            border: 1px dashed #b48b66;
            border-radius: 16px;
            padding: 1rem;
            margin-bottom: 1rem;
            line-height: 1.8;
        }

        .secure-note {
            font-size: 0.9rem;
            color: #6d5f55;
            margin-top: 0.5rem;
        }
    </style>
</head>

<body>

<div class="topbar">
    <div class="container">
        <span>Mini Online Bookstore</span>
        <span>Secure payment simulation for BookNest orders</span>
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
        <p class="eyebrow">Payment</p>
        <h1>Complete Payment</h1>
        <p>Payment method selected: <strong><?php echo htmlspecialchars($payment_method); ?></strong></p>
    </div>
</section>

<main class="section">
    <div class="container two-col">

        <div>
            <form class="form-card" method="POST" action="payment.php">
                <h2>Payment Details</h2>

                <?php if (!empty($errors)): ?>
                    <div class="notice" style="margin-bottom:1rem;">
                        <?php foreach ($errors as $error): ?>
                            <p><?php echo htmlspecialchars($error); ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if ($payment_method === 'Online Transfer'): ?>

                    <div class="payment-method-box">
                        <h3>Online Transfer Information</h3>

                        <div class="bank-box">
                            <strong>Bank:</strong> Maybank<br>
                            <strong>Account Name:</strong> BookNest Enterprise<br>
                            <strong>Account Number:</strong> 1234567890<br>
                            <strong>Amount to Pay:</strong> RM<?php echo number_format($total, 2); ?>
                        </div>

                        <div class="field">
                            <label>Payment Reference Number</label>
                            <input 
                                class="input" 
                                type="text" 
                                name="payment_reference" 
                                value="<?php echo htmlspecialchars($payment_reference); ?>"
                                placeholder="Example: MBB123456789"
                                required
                            >
                        </div>

                        <p class="secure-note">
                            This is a payment simulation. The order will be marked as Pending Verification.
                        </p>
                    </div>

                <?php endif; ?>

                <?php if ($payment_method === 'Credit/Debit Card'): ?>

                    <div class="payment-method-box">
                        <h3>Credit/Debit Card Information</h3>

                        <div class="field">
                            <label>Cardholder Name</label>
                            <input 
                                class="input" 
                                type="text" 
                                name="card_name" 
                                value="<?php echo htmlspecialchars($card_name); ?>"
                                placeholder="Name on card"
                                required
                            >
                        </div>

                        <div class="field">
                            <label>Card Number</label>
                            <input 
                                class="input" 
                                type="text" 
                                name="card_number" 
                                value="<?php echo htmlspecialchars($card_number); ?>"
                                placeholder="1234 5678 9012 3456"
                                maxlength="19"
                                required
                            >
                        </div>

                        <div class="field">
                            <label>Expiry Date</label>
                            <input 
                                class="input" 
                                type="text" 
                                name="expiry" 
                                value="<?php echo htmlspecialchars($expiry); ?>"
                                placeholder="MM/YY"
                                maxlength="5"
                                required
                            >
                        </div>

                        <div class="field">
                            <label>CVV</label>
                            <input 
                                class="input" 
                                type="password" 
                                name="cvv" 
                                value="<?php echo htmlspecialchars($cvv); ?>"
                                placeholder="123"
                                maxlength="4"
                                required
                            >
                        </div>

                        <p class="secure-note">
                            This is a payment simulation. The system only stores the last 4 digits of the card number.
                        </p>
                    </div>

                <?php endif; ?>

                <button class="btn" type="submit">
                    Confirm Payment
                </button>

                <a class="btn secondary" href="checkout.php" style="margin-top:0.75rem;">
                    Back to Checkout
                </a>
            </form>
        </div>

        <aside class="summary">
            <h2>Order Summary</h2>

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

            <div class="notice" style="margin-top:1rem;">
                <strong>Delivery To:</strong><br>
                <?php echo htmlspecialchars($checkout['name']); ?><br>
                <?php echo htmlspecialchars($checkout['contact']); ?><br>
                <?php echo htmlspecialchars($checkout['address']); ?>
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