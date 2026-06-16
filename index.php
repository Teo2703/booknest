<?php 
include 'app.php';

// Get 3 most sold books from database
$featured = $conn->query("
    SELECT books.*, 
           COALESCE(SUM(order_items.quantity), 0) AS total_sold
    FROM books
    LEFT JOIN order_items ON books.book_id = order_items.book_id
    GROUP BY books.book_id
    ORDER BY total_sold DESC
    LIMIT 3
");

$featuredBooks = [];
while ($row = $featured->fetch_assoc()) {
    $featuredBooks[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home | BookNest</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <div class="topbar">
        <div class="container">
            <span>Mini Online Bookstore</span>
            <span>Free local delivery for orders above RM80</span>
        </div>
    </div>

    <?php include __DIR__ . '/includes/navigation.php'; ?>

    <section class="hero">
        <div class="container hero-grid">
            <div>
                <p class="eyebrow">Mini Online Bookstore</p>
                <h1>Find your next favourite book in one simple place.</h1>
                <p>BookNest helps customers browse books, compare details, add items to cart, and place orders through a clean web-based bookstore interface.</p>
                <div class="actions">
                    <a class="btn" href="books/books.php">Browse Books</a>
                </div>
            </div>
            <div class="hero-card">
                <?php foreach ($featuredBooks as $b): ?>
                    <div class="mock-book">
                        <span><?php echo htmlspecialchars($b['category']); ?></span>
                        <strong><?php echo htmlspecialchars($b['title']); ?></strong>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Featured Books — most sold, updates automatically -->
    <section class="section">
        <div class="container">
            <div class="section-head">
                <div>
                    <h2>Featured Books</h2>
                    <p>Most popular books in our store.</p>
                </div>
                <a class="btn secondary all-btn" href="books/books.php">View All</a>
            </div>
            <div class="grid grid-3">
                <?php if (empty($featuredBooks)): ?>
                    <p>No books available yet.</p>
                <?php endif; ?>

                <?php foreach ($featuredBooks as $b): ?>
                <article class="card">
                    <div class="book-cover"><?php echo htmlspecialchars($b['title']); ?></div>
                    <div class="card-body">
                        <span class="tag"><?php echo htmlspecialchars($b['category']); ?></span>
                        <h3 class="book-title"><?php echo htmlspecialchars($b['title']); ?></h3>
                        <p class="meta">by <?php echo htmlspecialchars($b['author']); ?></p>
                        <p class="price">RM<?php echo number_format($b['price'], 2); ?></p>
                        <a class="btn secondary detail-btn" href="books/book-detail.php?id=<?php echo $b['book_id']; ?>">View Details</a>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="container grid grid-4">
            <div class="card">
                <div class="card-body">
                    <h3>Easy Search</h3>
                    <p class="meta">Search books by title, author, or category.</p>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <h3>Shopping Cart</h3>
                    <p class="meta">Review selected books before checkout.</p>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <h3>Order Tracking</h3>
                    <p class="meta">Customers can view order history and status.</p>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <h3>Admin Control</h3>
                    <p class="meta">Admin can manage books and orders.</p>
                </div>
            </div>
        </div>
    </section>

    <footer class="footer">
        <div class="container footer-grid">
            <div>
                <h3>BookNest</h3>
                <p>A clean static prototype for the Mini Online Bookstore e-commerce system.</p>
            </div>
            <div>
                <h4>Customer</h4>
                <a href="books/books.php">Browse Books</a>
                <a href="orders/cart.php">Shopping Cart</a>
                <a href="orders/checkout.php">Checkout</a>
            </div>
            <div>
                <h4>Admin</h4>
                <a href="admin/admin-dashboard.php">Dashboard</a>
                <a href="admin/manage-books.php">Manage Books</a>
                <a href="admin/manage-orders.php">Manage Orders</a>
            </div>
        </div>
    </footer>

</body>
</html>