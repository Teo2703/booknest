<?php
include '../app.php';
requireAdmin();

$title = $_POST['title'];
$author = $_POST['author'];
$category = $_POST['category'];
$price = $_POST['price'];
$stock = $_POST['stock'];
$description = $_POST['description'];

$image = null;

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

$stmt = $conn->prepare("
    INSERT INTO books (title, author, category, price, stock, description, image)
    VALUES (?, ?, ?, ?, ?, ?, ?)
");

$stmt->bind_param("sssdiss", $title, $author, $category, $price, $stock, $description, $image);

if ($stmt->execute()) {
    header("Location: manage-books.php");
    exit();
} else {
    echo "Error adding book.";
}
?>