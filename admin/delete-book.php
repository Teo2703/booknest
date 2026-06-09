<?php
include '../app.php';
requireAdmin();

if (!isset($_GET['id'])) {
    die("Book ID is missing.");
}

$book_id = $_GET['id'];

$stmt = $conn->prepare("DELETE FROM books WHERE book_id = ?");
$stmt->bind_param("i", $book_id);

if ($stmt->execute()) {
    header("Location: manage-books.php");
    exit();
} else {
    echo "Error deleting book.";
}
?>