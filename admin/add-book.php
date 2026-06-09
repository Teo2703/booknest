<?php
include '../app.php';
requireAdmin();

$title = $_POST['title'];
$author = $_POST['author'];
$category = $_POST['category'];
$price = $_POST['price'];
$stock = $_POST['stock'];
$description = $_POST['description'];

$stmt = $conn->prepare("INSERT INTO books (title, author, category, price, stock, description) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("sssdis", $title, $author, $category, $price, $stock, $description);

if ($stmt->execute()) {
    header("Location: manage-books.php");
    exit();
} else {
    echo "Error adding book.";
}
?>