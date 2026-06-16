<?php
include '../app.php';
requireAdmin();

$search = isset($_GET['search']) ? $_GET['search'] : '';

if ($search != '') {
    $stmt = $conn->prepare("SELECT * FROM books 
                            WHERE title LIKE ? OR author LIKE ? OR category LIKE ?
                            ORDER BY book_id ASC");
    $keyword = "%$search%";
    $stmt->bind_param("sss", $keyword, $keyword, $keyword);
    $stmt->execute();
    $books = $stmt->get_result();
} else {
    $books = $conn->query("SELECT * FROM books ORDER BY book_id ASC");
}

/* LOW STOCK */
$lowStockQuery = $conn->query("
    SELECT title, stock 
    FROM books 
    WHERE stock <= 5
    ORDER BY stock ASC
");

$lowStockBooks = [];
while ($row = $lowStockQuery->fetch_assoc()) {
    $lowStockBooks[] = $row;
}

$lowStockCount = count($lowStockBooks);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Books | BookNest</title>
<link rel="stylesheet" href="../css/style.css?v=123">
</head>

<body>

<div class="topbar">
    <div class="container">
        <span>Mini Online Bookstore</span>
        <span>Admin book management</span>
    </div>
</div>

<header class="navbar">
    <div class="container nav-inner">
        <a class="brand" href="admin-dashboard.php">
            Book<span>Nest</span>
        </a>
    </div>
</header>

<section class="page-title">
    <div class="container">
        <h1>Manage Books</h1>
    </div>
</section>

<main class="section">
<div class="container admin-layout">

<aside class="sidebar">
    <a href="admin-dashboard.php">Dashboard</a>
    <a class="active" href="manage-books.php">Manage Books</a>
    <a href="manage-orders.php">Manage Orders</a>
    <a href="analytics.php">Analytics</a>
    <a href="../auth/logout.php">Logout</a>
</aside>

<section>

<!-- SEARCH -->
<form method="GET" class="actions">
    <input class="input" name="search" placeholder="Search book" value="<?php echo htmlspecialchars($search); ?>">
    <button class="btn secondary">Search</button>
</form>

<!-- 🔥 LOW STOCK STAT CARD -->
<div class="stat-grid">
    <div class="stat danger-card">
        <span>⚠ Low Stock</span><br>
        <strong><?php echo $lowStockCount; ?> Items</strong>
    </div>
</div>

<!-- TABLE -->
<div class="table-wrap">
<table>
<thead>
<tr>
<th>ID</th>
<th>Title</th>
<th>Author</th>
<th>Category</th>
<th>Price</th>
<th>Stock</th>
<th>Action</th>
</tr>
</thead>

<tbody>
<?php while ($book = $books->fetch_assoc()): ?>

<tr class="<?php 
    if ($book['stock'] <= 2) echo 'row-danger';
    elseif ($book['stock'] <= 5) echo 'row-warning';
?>">

<td>B<?php echo str_pad($book['book_id'], 3, '0', STR_PAD_LEFT); ?></td>
<td><?php echo htmlspecialchars($book['title']); ?></td>
<td><?php echo htmlspecialchars($book['author']); ?></td>
<td><?php echo htmlspecialchars($book['category']); ?></td>
<td>RM<?php echo number_format($book['price'], 2); ?></td>

<td>
    <?php if ($book['stock'] <= 2): ?>
        <span class="stock-pill danger">🔴<?php echo $book['stock']; ?> </span>

    <?php elseif ($book['stock'] <= 5): ?>
        <span class="stock-pill warning">🟡<?php echo $book['stock']; ?> </span>

    <?php else: ?>
        <span class="stock-pill normal">🟢<?php echo $book['stock']; ?></span>
    <?php endif; ?>
</td>

<td>
<a class="btn secondary edit-btn" href="edit-book.php?id=<?php echo $book['book_id']; ?>">Edit</a>
<a class="btn danger" href="delete-book.php?id=<?php echo $book['book_id']; ?>" onclick="return confirm('Delete this book?')">Delete</a>
</td>

</tr>

<?php endwhile; ?>
</tbody>
</table>
</div>

</section>
</div>
</main>

</body>
</html>