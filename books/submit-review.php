<?php
include '../app.php';
requireCustomer();

$user_id = (int)$_SESSION['user_id'];
$book_id = isset($_POST['book_id']) ? (int)$_POST['book_id'] : 0;
$order_id = isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0;
$rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;
$comment = trim($_POST['comment'] ?? '');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || $book_id <= 0 || $order_id <= 0) {
    header("Location: book-detail.php?id=" . $book_id);
    exit();
}

if ($rating < 1 || $rating > 5) {
    header("Location: book-detail.php?id=" . $book_id . "&review_error=rating");
    exit();
}

// Verify this user actually purchased this book in this completed order
$verifyStmt = $conn->prepare("
    SELECT order_items.order_id
    FROM order_items
    JOIN orders ON order_items.order_id = orders.order_id
    WHERE orders.order_id = ?
      AND orders.user_id = ?
      AND order_items.book_id = ?
      AND orders.status = 'Completed'
");
$verifyStmt->bind_param("iii", $order_id, $user_id, $book_id);
$verifyStmt->execute();
$verified = $verifyStmt->get_result()->fetch_assoc();

if (!$verified) {
    header("Location: book-detail.php?id=" . $book_id . "&review_error=not_eligible");
    exit();
}

try {
    $stmt = $conn->prepare("
        INSERT INTO reviews (book_id, user_id, order_id, rating, comment)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("iiiis", $book_id, $user_id, $order_id, $rating, $comment);
    $stmt->execute();

    header("Location: book-detail.php?id=" . $book_id . "&review_success=1");
    exit();
} catch (Exception $e) {
    // Likely the UNIQUE key — already reviewed this purchase
    header("Location: book-detail.php?id=" . $book_id . "&review_error=duplicate");
    exit();
}
?>