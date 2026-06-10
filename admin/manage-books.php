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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Books | BookNest</title>

    <link rel="stylesheet" href="../css/style.css?v=123">
</head>

<body>

    <!-- Top Bar -->
    <div class="topbar">
        <div class="container">
            <span>Mini Online Bookstore</span>
            <span>Admin book management</span>
        </div>
    </div>

    <!-- Navigation Bar -->
    <header class="navbar">
        <div class="container nav-inner">
            <a class="brand" href="index.php">
                Book<span>Nest</span>
            </a>

            <nav class="nav-links">
                <a href="../index.php">Home</a>
                <a href="../books/books.php">Books</a>
            </nav>
        </div>
    </header>

    <!-- Page Title -->
    <section class="page-title">
        <div class="container">
            <p class="eyebrow">Admin Area</p>
            <h1>Manage Books</h1>
            <p>Admin can add, edit, delete, and update book records.</p>
        </div>
    </section>

    <!-- Main Section -->
    <main class="section">
        <div class="container admin-layout">

            <!-- Sidebar -->
            <aside class="sidebar">
                <a href="admin-dashboard.php">Dashboard</a>
                <a class="active" href="manage-books.php">Manage Books</a>
                <a href="manage-orders.php">Manage Orders</a>
                <a href="../index.php">Logout</a>
            </aside>

            <!-- Manage Books Content -->
            <section>

                <!-- Action Area -->
                <form method="GET" class="actions" style="margin-top:0;margin-bottom:1rem">
                    <a class="btn" href="#add-book">Add New Book</a>
                    <input class="input" name="search" style="max-width:530px" placeholder="Search book record" value="<?php echo htmlspecialchars($search); ?>">
                    <button class="btn secondary search-btn" type="submit">Search</button>
                </form>

                <!-- Book Table -->
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Book Title</th>
                                <th>Author</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Stock</th>
                                <th>Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php while ($book = $books->fetch_assoc()): ?>
                            <tr>
                                <td>B<?php echo str_pad($book['book_id'], 3, '0', STR_PAD_LEFT); ?></td>
                                <td><?php echo htmlspecialchars($book['title']); ?></td>
                                <td><?php echo htmlspecialchars($book['author']); ?></td>
                                <td><?php echo htmlspecialchars($book['category']); ?></td>
                                <td>RM<?php echo number_format($book['price'], 2); ?></td>
                                <td><?php echo $book['stock']; ?></td>
                                <td>
                                    <a class="btn secondary edit-btn" href="edit-book.php?id=<?php echo $book['book_id']; ?>">Edit</a>
                                    <a class="btn danger" href="delete-book.php?id=<?php echo $book['book_id']; ?>" onclick="return confirm('Delete this book?')">Delete</a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Add Book Form -->
                <form id="add-book" class="form-card" style="margin-top:1.4rem" method="POST" action="add-book.php">
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
                                <option>Fiction</option>
                                <option>Academic</option>
                                <option>Children</option>
                                <option>Self-Improvement</option>
                                <option>Comics</option>
                            </select>
                        </div>

                        <div class="field">
                            <label>Price</label>
                            <input class="input" name="price" type="number" step="0.01" placeholder="RM" required>
                        </div>

                        <div class="field">
                            <label>Stock</label>
                            <input class="input" name="stock" type="number" placeholder="Quantity" required>
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

    <!-- Footer -->
    <footer class="footer">
        <div class="container footer-grid">

            <div>
                <h3>BookNest</h3>
                <p>A clean static prototype for the Mini Online Bookstore e-commerce system.</p>
            </div>

            <div>
                <h4>Customer</h4>
                <a href="../books/books.php">Browse Books</a>
                <a href="../orders/cart.php">Shopping Cart</a>
                <a href="../orders/checkout.php">Checkout</a>
            </div>

            <div>
                <h4>Admin</h4>
                <a href="admin-dashboard.php">Dashboard</a>
                <a href="manage-books.php">Manage Books</a>
                <a href="manage-orders.php">Manage Orders</a>
            </div>

        </div>
    </footer>

</body>
</html>