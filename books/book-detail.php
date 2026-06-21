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

// Check if this customer purchased and completed an order with this book, and hasn't reviewed yet
$canReview = false;
$eligibleOrderId = 0;

if (isCustomer()) {
    $eligibleStmt = $conn->prepare("
        SELECT order_items.order_id
        FROM order_items
        JOIN orders ON order_items.order_id = orders.order_id
        LEFT JOIN reviews ON reviews.order_id = orders.order_id 
            AND reviews.book_id = order_items.book_id
        WHERE orders.user_id = ?
          AND order_items.book_id = ?
          AND orders.status = 'Completed'
          AND reviews.review_id IS NULL
        LIMIT 1
    ");
    $eligibleStmt->bind_param("ii", $_SESSION['user_id'], $book['book_id']);
    $eligibleStmt->execute();
    $eligible = $eligibleStmt->get_result()->fetch_assoc();

    if ($eligible) {
        $canReview = true;
        $eligibleOrderId = (int)$eligible['order_id'];
    }
}

// Get existing reviews for this book
$reviewsStmt = $conn->prepare("
    SELECT reviews.rating, reviews.comment, reviews.created_at, users.name
    FROM reviews
    JOIN orders ON reviews.order_id = orders.order_id
    JOIN users ON orders.user_id = users.user_id
    WHERE reviews.book_id = ?
    ORDER BY reviews.created_at DESC
");
$reviewsStmt->bind_param("i", $book['book_id']);
$reviewsStmt->execute();
$bookReviews = $reviewsStmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($book['title']); ?> | BookNest</title>
    <link rel="stylesheet" href="../css/style.css?v=126">
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
            <p class="eyebrow">Book Details</p>
            <h1><?php echo htmlspecialchars($book['title']); ?></h1>
            <p>View complete book information before adding it to the shopping cart.</p>
        </div>
    </section>

    <main class="section">
        <div class="container two-col">

            <?php
            $imageFile = __DIR__ . '/../uploads/books/' . $book['image'];
            $imagePath = '../uploads/books/' . $book['image'];
            ?>

            <div class="detail-cover">
                <?php if (!empty($book['image']) && file_exists($imageFile)): ?>
                    <img src="<?php echo htmlspecialchars($imagePath); ?>" alt="<?php echo htmlspecialchars($book['title']); ?>">
                <?php else: ?>
                    <span><?php echo htmlspecialchars($book['title']); ?></span>
                <?php endif; ?>
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

                <div class="actions2">
                    <?php if (isCustomer() && $book['stock'] > 0): ?>
                        <a class="btn" href="../orders/cart.php?add=<?php echo $book['book_id']; ?>">
                            Add to Cart
                        </a>
                    <?php elseif (!isLoggedIn()): ?>
                        <a class="btn" href="../auth/login.php">
                            Login to Buy
                        </a>
                    <?php else: ?>
                        <button class="btn" disabled>Out of Stock</button>
                    <?php endif; ?>
                    
                    <a class="btn secondary" href="books.php">Back</a>
                </div>
            </div>

        </div>

        <div class="container">
            <div class="form-card" style="margin-top:1.5rem;" id="reviews">
                <h2>Reviews</h2>

                <?php if (isset($_GET['review_success'])): ?>
                    <div class="notice">Thank you for your review!</div>
                <?php endif; ?>

                <?php if (isset($_GET['review_error'])): ?>
                    <div class="notice">
                        <?php
                        if ($_GET['review_error'] === 'duplicate') echo "You've already reviewed this purchase.";
                        elseif ($_GET['review_error'] === 'rating') echo "Please select a rating.";
                        else echo "You can only review books you've purchased and received.";
                        ?>
                    </div>
                <?php endif; ?>

                <?php if ($canReview): ?>
                    <form method="POST" action="submit-review.php" style="margin-bottom:1.5rem;">
                        <input type="hidden" name="book_id" value="<?php echo $book['book_id']; ?>">
                        <input type="hidden" name="order_id" value="<?php echo $eligibleOrderId; ?>">

                        <div class="field">
                            <label>Your Rating</label>
                            <select name="rating" required>
                                <option value="">Select rating</option>
                                <option value="5">5 - Excellent</option>
                                <option value="4">4 - Good</option>
                                <option value="3">3 - Average</option>
                                <option value="2">2 - Poor</option>
                                <option value="1">1 - Very Poor</option>
                            </select>
                        </div>

                        <div class="field">
                            <label>Your Review</label>
                            <textarea name="comment" rows="3" placeholder="Share your thoughts about this book"></textarea>
                        </div>

                        <button class="btn" type="submit">Submit Review</button>
                    </form>
                <?php elseif (!isLoggedIn()): ?>
                    <p class="small">Login and purchase this book to leave a review.</p>
                <?php elseif (isCustomer()): ?>
                    <p class="small">You can review this book after your order is completed.</p>
                <?php endif; ?>

                <?php if ($bookReviews->num_rows === 0): ?>
                    <p class="small">No reviews yet.</p>
                <?php else: ?>
                    <?php while ($r = $bookReviews->fetch_assoc()): ?>
                        <div class="info-row" style="flex-direction:column; align-items:flex-start; gap:0.3rem;">
                            <strong><?php echo str_repeat('★', (int)$r['rating']) . str_repeat('☆', 5 - (int)$r['rating']); ?></strong>
                            <span><?php echo htmlspecialchars($r['comment']); ?></span>
                            <span class="small">by <?php echo htmlspecialchars($r['name']); ?> on <?php echo date("d M Y", strtotime($r['created_at'])); ?></span>
                        </div>
                    <?php endwhile; ?>
                <?php endif; ?>
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