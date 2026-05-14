<?php
include '../app.php';

if (!isset($_GET['id'])) {
    header("Location: books.php");
    exit();
}

$book_id = $_GET['id'];
$stmt    = $conn->prepare("SELECT * FROM books WHERE book_id = ?");
$stmt->bind_param("i", $book_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Book not found.");
}

$book = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($book['title']); ?> | BookNest</title>
    <link rel="stylesheet" href="../css/style.css">
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
                <a class="active" href="books.php">Books</a>
                <a href="../group.html">Group</a>

                <?php if (!isLoggedIn()): ?>
                    <a href="../auth/register.php">Register</a>
                    <a href="../auth/login.php">Login</a>

                <?php elseif (isAdmin()): ?>
                    <a href="../admin/admin-dashboard.php">Dashboard</a>
                    <a href="../auth/logout.php">Logout</a>

                <?php else: ?>
                    <a href="../orders/cart.php">Cart</a>
                    <a href="../orders/order-history.php">Orders</a>
                    <a href="../auth/logout.php">
                        👤 <?php echo htmlspecialchars($_SESSION['user_name']); ?> | Logout
                    </a>

                <?php endif; ?>
            </nav>
        </div>
    </header>

    <section class="page-title">
        <div class="container">
            <p class="eyebrow">Book Details</p>
            <h1><?php echo htmlspecialchars($book['title']); ?></h1>
            <p>View complete book information before adding it to the shopping cart.</p>
        </div>
    </section>

    <main class="section">
        <div class="container two-col">

            <div class="detail-cover">
                <?php echo htmlspecialchars($book['title']); ?>
            </div>

            <div class="form-card">
                <span class="tag"><?php echo htmlspecialchars($book['category']); ?></span>
                <h2><?php echo htmlspecialchars($book['title']); ?></h2>
                <p class="meta">by <?php echo htmlspecialchars($book['author']); ?></p>
                <p><?php echo htmlspecialchars($book['description']); ?></p>

                <div>
                    <div class="info-row">
                        <strong>Price</strong>
                        <span>RM<?php echo number_format($book['price'], 2); ?></span>
                    </div>
                    <div class="info-row">
                        <strong>Stock</strong>
                        <span>
                            <?php if ($book['stock'] > 0): ?>
                                <span class="status completed">Available</span>
                            <?php else: ?>
                                <span class="status cancelled">Out of Stock</span>
                            <?php endif; ?>
                        </span>
                    </div>
                    <div class="info-row">
                        <strong>Category</strong>
                        <span><?php echo htmlspecialchars($book['category']); ?></span>
                    </div>
                </div>

                <div class="actions">
                    <?php if (isCustomer() && $book['stock'] > 0): ?>
                        <a class="btn" href="../orders/cart.php?add=<?php echo $book['book_id']; ?>">Add to Cart</a>
                    <?php elseif (!isLoggedIn()): ?>
                        <a class="btn" href="../auth/login.php">Login to Buy</a>
                    <?php else: ?>
                        <button class="btn" disabled>Out of Stock</button>
                    <?php endif; ?>
                    <a class="btn secondary" href="books.php">Back to Books</a>
                </div>
            </div>

        </div>
    </main>

    <footer class="footer">
        <div class="container footer-grid">
            <div>
                <h3>BookNest</h3>
                <p>A clean static prototype for the Mini Online Bookstore e-commerce system.</p>
            </div>
            <div>
                <h4>Customer</h4>
                <a href="books.php">Browse Books</a>
                <a href="../orders/cart.php">Shopping Cart</a>
                <a href="../orders/checkout.php">Checkout</a>
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