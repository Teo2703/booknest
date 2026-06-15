<?php
include '../app.php';
requireCustomer();

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$message = '';
$error = '';

function cleanCartQuantity($quantity) {
    $quantity = (int)$quantity;
    return $quantity < 1 ? 1 : $quantity;
}

// Add book to cart from books page or book detail page
if (isset($_GET['add'])) {
    $book_id = (int)$_GET['add'];

    $stmt = $conn->prepare("SELECT book_id, stock FROM books WHERE book_id = ?");
    $stmt->bind_param("i", $book_id);
    $stmt->execute();
    $book = $stmt->get_result()->fetch_assoc();

    if (!$book) {
        $error = "Book not found.";
    } elseif ((int)$book['stock'] <= 0) {
        $error = "This book is currently out of stock.";
    } else {
        $currentQty = isset($_SESSION['cart'][$book_id]) ? (int)$_SESSION['cart'][$book_id] : 0;

        if ($currentQty >= (int)$book['stock']) {
            $error = "Cannot add more than available stock.";
        } else {
            $_SESSION['cart'][$book_id] = $currentQty + 1;
            $message = "Book added to cart successfully.";
        }
    }
}

// Remove one book from cart
if (isset($_GET['remove'])) {
    $book_id = (int)$_GET['remove'];
    unset($_SESSION['cart'][$book_id]);
    $message = "Book removed from cart.";
}

// Clear whole cart
if (isset($_GET['clear'])) {
    $_SESSION['cart'] = [];
    $message = "Cart cleared.";
}

// Update cart quantities
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_cart'])) {
    $quantities = $_POST['quantity'] ?? [];

    foreach ($quantities as $book_id => $quantity) {
        $book_id = (int)$book_id;
        $quantity = cleanCartQuantity($quantity);

        $stmt = $conn->prepare("SELECT stock FROM books WHERE book_id = ?");
        $stmt->bind_param("i", $book_id);
        $stmt->execute();
        $book = $stmt->get_result()->fetch_assoc();

        if (!$book) {
            unset($_SESSION['cart'][$book_id]);
            continue;
        }

        if ($quantity > (int)$book['stock']) {
            $quantity = (int)$book['stock'];
            $error = "Some quantities were adjusted based on available stock.";
        }

        $_SESSION['cart'][$book_id] = $quantity;
    }

    if ($error === '') {
        $message = "Cart updated successfully.";
    }
}

$cartItems = [];
$subtotal = 0;

if (!empty($_SESSION['cart'])) {
    $bookIds = array_keys($_SESSION['cart']);
    $placeholders = implode(',', array_fill(0, count($bookIds), '?'));
    $types = str_repeat('i', count($bookIds));

    $stmt = $conn->prepare("
        SELECT book_id, title, author, category, price, stock 
        FROM books 
        WHERE book_id IN ($placeholders)
    ");

    $stmt->bind_param($types, ...$bookIds);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($book = $result->fetch_assoc()) {
        $book_id = (int)$book['book_id'];
        $quantity = isset($_SESSION['cart'][$book_id]) ? (int)$_SESSION['cart'][$book_id] : 0;

        if ($quantity < 1) {
            unset($_SESSION['cart'][$book_id]);
            continue;
        }

        if ($quantity > (int)$book['stock']) {
            $quantity = (int)$book['stock'];
            $_SESSION['cart'][$book_id] = $quantity;
        }

        if ($quantity < 1) {
            unset($_SESSION['cart'][$book_id]);
            continue;
        }

        $lineTotal = (float)$book['price'] * $quantity;
        $subtotal += $lineTotal;

        $cartItems[] = [
            'book' => $book,
            'quantity' => $quantity,
            'line_total' => $lineTotal
        ];
    }
}

$delivery = $subtotal >= 80 || $subtotal == 0 ? 0 : 5;
$total = $subtotal + $delivery;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart | BookNest</title>
    <link rel="stylesheet" href="../css/style.css?v=130">

    <style>
        .cart-layout {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 330px;
            gap: 1.5rem;
            align-items: flex-start;
        }

        .cart-panel {
            background: #fffdf8;
            border: 1px solid #e7d8c8;
            border-radius: 24px;
            overflow: hidden;
        }

        .cart-header,
        .cart-row {
            display: grid;
            grid-template-columns: minmax(180px, 2fr) minmax(90px, 1fr) minmax(90px, 1fr) minmax(100px, 1fr);
            gap: 0.75rem;
            align-items: center;
        }

        .cart-header {
            padding: 1rem 1.4rem;
            background: #fff8ef;
            border-bottom: 1px solid #e7d8c8;
            font-weight: 800;
            color: #5b321c;
            font-size: 1.05rem;
        }

        .cart-row {
            padding: 1.2rem 1.4rem;
            border-bottom: 1px solid #e7d8c8;
        }

        .cart-row:last-child {
            border-bottom: none;
        }

        .cart-book-title {
            font-weight: 800;
            color: #222;
            margin-bottom: 0.3rem;
        }

        .cart-book-meta {
            color: #6d5f55;
            font-size: 0.95rem;
            margin-bottom: 0.4rem;
        }

        .cart-remove-link {
            display: inline-block;
            color: #8b1e1e;
            font-weight: 700;
            text-decoration: none;
            font-size: 0.9rem;
        }

        .cart-remove-link:hover {
            text-decoration: underline;
        }

        .cart-price,
        .cart-subtotal {
            font-weight: 700;
            color: #222;
        }

        .cart-qty-input {
            width: 76px;
            height: 44px;
            padding: 0.4rem;
            text-align: center;
        }

        .cart-actions {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 0.6rem;
            padding: 1rem 1.4rem;
            border-top: 1px solid #e7d8c8;
            background: #fffdf8;
        }

        .cart-actions .btn {
            width: 100%;
            min-width: 0;
            height: 48px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0 0.5rem;
            font-size: 0.9rem;
            font-weight: 700;
            text-align: center;
            box-sizing: border-box;
            white-space: normal;
            line-height: 1.2;
        }

        .cart-actions button.btn {
            border: none;
            cursor: pointer;
            font-family: inherit;
        }

        @media (max-width: 1000px) {
            .cart-layout {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 700px) {
            .cart-header {
                display: none;
            }

            .cart-row {
                grid-template-columns: 1fr;
                gap: 0.6rem;
            }

            .cart-price::before {
                content: "Price: ";
                font-weight: 800;
                color: #5b321c;
            }

            .cart-subtotal::before {
                content: "Subtotal: ";
                font-weight: 800;
                color: #5b321c;
            }

            .cart-actions {
                grid-template-columns: 1fr;
            }

            .cart-qty-input {
                width: 100%;
                max-width: 120px;
            }
        }
    </style>
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
            <a class="active" href="cart.php">Cart</a>
            <a href="order-history.php">Orders</a>
            <a href="../auth/logout.php">
                👤 <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Customer'); ?> | Logout
            </a>
        </nav>
    </div>
</header>

<section class="page-title">
    <div class="container">
        <p class="eyebrow">Shopping Cart</p>
        <h1>Your Cart</h1>
        <p>Review selected books before checkout. You can update quantity or remove items.</p>
    </div>
</section>

<main class="section">

    <div class="container">
        <?php if ($message !== ''): ?>
            <div class="notice" style="margin-bottom:1rem;">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <?php if ($error !== ''): ?>
            <div class="notice" style="margin-bottom:1rem;">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="container cart-layout">

        <div class="cart-panel">
            <?php if (empty($cartItems)): ?>

                <div class="notice" style="margin:1rem;">
                    Your shopping cart is empty. Please browse books and add items to cart.
                </div>

            <?php else: ?>

                <form method="POST" action="cart.php">

                    <div class="cart-header">
                        <div>Book</div>
                        <div>Price</div>
                        <div>Quantity</div>
                        <div>Subtotal</div>
                    </div>

                    <?php foreach ($cartItems as $item): ?>
                        <?php
                            $book = $item['book'];
                            $bookId = (int)$book['book_id'];
                        ?>

                        <div class="cart-row">
                            <div>
                                <div class="cart-book-title">
                                    <?php echo htmlspecialchars($book['title']); ?>
                                </div>

                                <div class="cart-book-meta">
                                    <?php echo htmlspecialchars($book['category']); ?>
                                    |
                                    Stock: <?php echo (int)$book['stock']; ?>
                                </div>

                                <a class="cart-remove-link" href="cart.php?remove=<?php echo $bookId; ?>">
                                    Remove
                                </a>
                            </div>

                            <div class="cart-price">
                                RM<?php echo number_format($book['price'], 2); ?>
                            </div>

                            <div>
                                <input 
                                    class="input cart-qty-input"
                                    type="number"
                                    name="quantity[<?php echo $bookId; ?>]"
                                    value="<?php echo (int)$item['quantity']; ?>"
                                    min="1"
                                    max="<?php echo (int)$book['stock']; ?>"
                                >
                            </div>

                            <div class="cart-subtotal">
                                RM<?php echo number_format($item['line_total'], 2); ?>
                            </div>
                        </div>

                    <?php endforeach; ?>

                    <div class="cart-actions">
                        <a class="btn secondary" href="../books/books.php">
                            Continue Shopping
                        </a>

                        <a class="btn danger" href="cart.php?clear=1">
                            Clear Cart
                        </a>

                        <button class="btn" type="submit" name="update_cart">
                            Update Cart
                        </button>
                    </div>

                </form>

            <?php endif; ?>
        </div>

        <aside class="summary">
            <h2>Order Summary</h2>

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

            <?php if (!empty($cartItems)): ?>
                <a class="btn" style="width:100%;margin-top:1rem" href="checkout.php">
                    Proceed to Checkout
                </a>
            <?php else: ?>
                <a class="btn" style="width:100%;margin-top:1rem" href="../books/books.php">
                    Browse Books
                </a>
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