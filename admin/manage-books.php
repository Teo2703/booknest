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
<link rel="stylesheet" href="../css/style.css?v=124">
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

<!-- SEARCH + ADD BOOK SHORTCUT -->
<form method="GET" class="actions" style="margin-top:0;margin-bottom:1rem">
    <a class="btn" href="#add-book">Add New Book</a>

    <input 
        class="input" 
        name="search" 
        style="max-width:530px" 
        placeholder="Search book" 
        value="<?php echo htmlspecialchars($search); ?>"
    >

    <button class="btn secondary search-btn" type="submit">Search</button>
</form>

<<<<<<< HEAD
<!-- 🔥 LOW STOCK STAT CARD -->
<div class="stat-grid">
    <div class="stat danger-card">
        <span>⚠ Low Stock</span><br>
        <strong><?php echo $lowStockCount; ?> Items</strong>
    </div>
=======
<!-- LOW STOCK WARNING -->
<?php if (!empty($lowStockBooks)): ?>
<div class="low-stock-card">
    <h3>⚠ Low Stock Warning</h3>

    <?php foreach ($lowStockBooks as $book): ?>
        <div class="low-stock-item">
            <span><?php echo htmlspecialchars($book['title']); ?></span>

            <span class="stock-badge 
                <?php echo ($book['stock'] <= 2) ? 'danger' : 'warning'; ?>">
                <?php echo $book['stock']; ?> left
            </span>
        </div>
    <?php endforeach; ?>

>>>>>>> fc8c952bb0cb8f77de5c6c1f4b6070b1abc52470
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

<<<<<<< HEAD
=======
<!-- STOCK COLOR -->
>>>>>>> fc8c952bb0cb8f77de5c6c1f4b6070b1abc52470
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
<<<<<<< HEAD
<a class="btn danger" href="delete-book.php?id=<?php echo $book['book_id']; ?>" onclick="return confirm('Delete this book?')">Delete</a>
=======
<a 
    class="btn danger" 
    href="delete-book.php?id=<?php echo $book['book_id']; ?>" 
    onclick="return confirm('Delete this book?')"
>Delete</a>
>>>>>>> fc8c952bb0cb8f77de5c6c1f4b6070b1abc52470
</td>

</tr>

<?php endwhile; ?>
</tbody>
</table>
</div>

<!-- ADD BOOK FORM -->
<form 
    id="add-book" 
    class="form-card" 
    style="margin-top:1.4rem" 
    method="POST" 
    action="add-book.php" 
    enctype="multipart/form-data" 
    novalidate
>
    <h2>Add Book Form</h2>

    <div class="form-grid">
        <div class="field">
            <label>Title</label>
            <input class="input" name="title" placeholder="Book title" required>
        </div>

        <div class="field">
            <label>Author</label>
            <input class="input" name="author" placeholder="Author name" required>
        </div>

        <div class="field">
            <label>Category</label>
            <select name="category" required>
                <option value="Fiction">Fiction</option>
                <option value="Academic">Academic</option>
                <option value="Children">Children</option>
                <option value="Self-Improvement">Self-Improvement</option>
                <option value="Comics">Comics</option>
            </select>
        </div>

        <div class="field">
            <label>Price</label>
            <input class="input" name="price" type="number" step="0.01" min="0" placeholder="RM" required>
        </div>

        <div class="field">
            <label>Stock</label>
            <input class="input" name="stock" type="number" min="0" placeholder="Quantity" required>
        </div>

        <div class="field">
            <label>Book Image</label>
            <input class="input" name="image" type="file" accept="image/*">
        </div>
    </div>

    <div class="field">
        <label>Description</label>
        <textarea name="description" rows="4" placeholder="Book description"></textarea>
    </div>

    <button class="btn" type="submit">Save Book</button>
</form>

</section>
</div>
</main>

<script src="../js/validation.js"></script>
</body>
</html>
