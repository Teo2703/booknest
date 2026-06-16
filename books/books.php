<?php
include '../app.php';

$search   = isset($_GET['search']) ? trim($_GET['search']) : '';
$category = isset($_GET['category']) ? trim($_GET['category']) : '';
$sort     = isset($_GET['sort']) ? trim($_GET['sort']) : 'latest';

$sql    = "SELECT * FROM books WHERE 1";
$params = [];
$types  = "";

if ($search != '') {
    $sql .= " AND (title LIKE ? OR author LIKE ?)";
    $keyword  = "%$search%";
    $params[] = $keyword;
    $params[] = $keyword;
    $types   .= "ss";
}

if ($category != '') {
    $sql .= " AND category = ?";
    $params[] = $category;
    $types   .= "s";
}

if ($sort == 'price_low') {
    $sql .= " ORDER BY price ASC";
} elseif ($sort == 'price_high') {
    $sql .= " ORDER BY price DESC";
} else {
    $sql .= " ORDER BY book_id DESC";
}

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$books = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Books | BookNest</title>
    <link rel="stylesheet" href="../css/style.css?v=124">
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
            <p class="eyebrow">Book Catalogue</p>
            <h1>Browse Books</h1>
            <p>Search and filter books before adding them to your cart.</p>
        </div>
    </section>

    <main class="section">
        <div class="container">

            <form method="GET" class="filters">
                <input class="input" name="search" placeholder="Search by title or author" value="<?php echo htmlspecialchars($search); ?>">
                <select name="category">
                    <option value="">All Categories</option>
                    <option value="Fiction"          <?php if($category=="Fiction")          echo "selected"; ?>>Fiction</option>
                    <option value="Academic"         <?php if($category=="Academic")         echo "selected"; ?>>Academic</option>
                    <option value="Children"         <?php if($category=="Children")         echo "selected"; ?>>Children</option>
                    <option value="Self-Improvement" <?php if($category=="Self-Improvement") echo "selected"; ?>>Self-Improvement</option>
                    <option value="Comics"           <?php if($category=="Comics")           echo "selected"; ?>>Comics</option>
                </select>
                <select name="sort">
                    <option value="latest"     <?php if($sort=="latest")     echo "selected"; ?>>Sort by Latest</option>
                    <option value="price_low"  <?php if($sort=="price_low")  echo "selected"; ?>>Price: Low to High</option>
                    <option value="price_high" <?php if($sort=="price_high") echo "selected"; ?>>Price: High to Low</option>
                </select>
                <button class="btn secondary searchbook-btn" type="submit">Search</button>
            </form>

            <div class="grid grid-3">
                <?php if ($books->num_rows === 0): ?>
                    <p>No books found.</p>
                <?php endif; ?>

                <?php while ($book = $books->fetch_assoc()): ?>
                <article class="card">
                    <?php
                    $imageFile = __DIR__ . '/../uploads/books/' . $book['image'];
                    $imagePath = '../uploads/books/' . $book['image'];
                    ?>

                    <div class="book-cover">
                        <?php if (!empty($book['image']) && file_exists($imageFile)): ?>
                            <img src="<?php echo htmlspecialchars($imagePath); ?>" alt="<?php echo htmlspecialchars($book['title']); ?>">
                        <?php else: ?>
                            <span><?php echo htmlspecialchars($book['title']); ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <span class="tag"><?php echo htmlspecialchars($book['category']); ?></span>
                        <h3 class="book-title"><?php echo htmlspecialchars($book['title']); ?></h3>
                        <p class="meta">by <?php echo htmlspecialchars($book['author']); ?></p>
                        <p class="price">RM<?php echo number_format($book['price'], 2); ?></p>
                        <div class="actions1">
                            <a class="btn secondary view-btn" href="book-detail.php?id=<?php echo $book['book_id']; ?>">
                                View Details
                            </a>

                            <?php if (isCustomer()): ?>
                                <a class="btn" href="../orders/cart.php?add=<?php echo $book['book_id']; ?>">
                                    Add to Cart
                                </a>

                            <?php elseif (!isLoggedIn()): ?>
                                <a class="btn" href="../auth/login.php">
                                    Login to Buy
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </article>
                <?php endwhile; ?>
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