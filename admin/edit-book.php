<?php
include '../app.php';
requireAdmin();

if (!isset($_GET['id'])) {
    die("Book ID is missing.");
}

$book_id = $_GET['id'];

/* Get existing book details */
$stmt = $conn->prepare("SELECT * FROM books WHERE book_id = ?");
$stmt->bind_param("i", $book_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Book not found.");
}

$book = $result->fetch_assoc();

/* Update book when form is submitted */
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $author = $_POST['author'];
    $category = $_POST['category'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $description = $_POST['description'];

    $image = $book['image'];

    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['jpg', 'jpeg', 'png', 'webp'];
        $originalName = $_FILES['image']['name'];
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        if (!in_array($extension, $allowedTypes)) {
            die("Only JPG, JPEG, PNG, and WEBP images are allowed.");
        }

        $image = uniqid('book_', true) . '.' . $extension;

        $uploadDir = __DIR__ . '/../uploads/books/';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $image);
    }

    $updateStmt = $conn->prepare("
        UPDATE books
        SET title = ?, author = ?, category = ?, price = ?, stock = ?, description = ?, image = ?
        WHERE book_id = ?
    ");

    $updateStmt->bind_param(
        "sssdissi",
        $title,
        $author,
        $category,
        $price,
        $stock,
        $description,
        $image,
        $book_id
    );

    if ($updateStmt->execute()) {
        header("Location: manage-books.php");
        exit();
    } else {
        echo "Error updating book.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Book | BookNest</title>
    <link rel="stylesheet" href="../css/style.css?v=2">
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
        <a class="brand" href="index.html">Book<span>Nest</span></a>
        <nav class="nav-links">
            <a href="../index.php">Home</a>
            <a href="../books/books.php">Books</a>
            <a href="../group.html">Group</a>
            <a href="../login.html">Login</a>
        </nav>
    </div>
</header>

<section class="page-title">
    <div class="container">
        <p class="eyebrow">Admin Area</p>
        <h1>Edit Book</h1>
        <p>Update selected book information.</p>
    </div>
</section>

<main class="section">
    <div class="container admin-layout">

    <?php include __DIR__ . '/../includes/admin-sidebar.php'; ?>

        <section>
           <form id="edit-book" class="form-card" method="POST" enctype="multipart/form-data" novalidate>
                <h2>Edit Book Form</h2>

                <div class="form-grid">
                    <div class="field">
                        <label>Title</label>
                        <input
                            class="input"
                            name="title"
                            value="<?php echo htmlspecialchars($book['title']); ?>"
                            required
                        >
                    </div>

                    <div class="field">
                        <label>Author</label>
                        <input
                            class="input"
                            name="author"
                            value="<?php echo htmlspecialchars($book['author']); ?>"
                            required
                        >
                    </div>

                    <div class="field">
                        <label>Category</label>
                        <select name="category" required>
                            <option value="Fiction" <?php if($book['category'] == "Fiction") echo "selected"; ?>>Fiction</option>
                            <option value="Academic" <?php if($book['category'] == "Academic") echo "selected"; ?>>Academic</option>
                            <option value="Children" <?php if($book['category'] == "Children") echo "selected"; ?>>Children</option>
                            <option value="Self-Improvement" <?php if($book['category'] == "Self-Improvement") echo "selected"; ?>>Self-Improvement</option>
                            <option value="Comics" <?php if($book['category'] == "Comics") echo "selected"; ?>>Comics</option>
                        </select>
                    </div>

                    <div class="field">
                        <label>Price</label>
                        <input
                            class="input"
                            name="price"
                            type="number"
                            step="0.01"
                            value="<?php echo htmlspecialchars($book['price']); ?>" 
                            required
                        >
                    </div>

                    <div class="field">
                        <label>Stock</label>
                        <input
                            class="input"
                            name="stock"
                            type="number"
                            value="<?php echo htmlspecialchars($book['stock']); ?>"
                            required
                        >
                    </div>
                </div>

                <div class="field">
                    <label>Book Image</label>

                    <?php if (!empty($book['image'])): ?>
                        <p class="meta">Current image: <?php echo htmlspecialchars($book['image']); ?></p>
                        <img 
                            src="../uploads/books/<?php echo htmlspecialchars($book['image']); ?>" 
                            alt="<?php echo htmlspecialchars($book['title']); ?>" 
                            style="width:120px;height:90px;object-fit:cover;border-radius:12px;margin-bottom:10px;"
                        >
                    <?php endif; ?>

                    <input class="input" name="image" type="file" accept="image/*">
                </div>

                <div class="field">
                    <label>Description</label>
                    <textarea name="description" rows="4"><?php echo htmlspecialchars($book['description']); ?></textarea>
                </div>

                <div class="actions1">
                    <button class="btn" type="submit">Update Book</button>
                    <a class="btn secondary cancel-btn" href="manage-books.php">Cancel</a>
                </div>
            </form>
        </section>

    </div>
</main>
<script src="../js/validation.js"></script>
</body>
</html>