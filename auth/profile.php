<?php
include '../app.php';

requireCustomer();

$user_id = $_SESSION["user_id"];

$stmt = $conn->prepare("SELECT user_id, name, email, contact, role, created_at FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    header("Location: logout.php");
    exit();
}

if ($user["role"] !== "customer") {
    header("Location: ../admin/admin-dashboard.php");
    exit();
}

$orderStmt = $conn->prepare("SELECT COUNT(*) AS total_orders FROM orders WHERE user_id = ?");
$orderStmt->bind_param("i", $user_id);
$orderStmt->execute();
$orderCount = $orderStmt->get_result()->fetch_assoc()["total_orders"];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Profile | BookNest</title>
    <link rel="stylesheet" href="../css/style.css?v=999">
</head>
<body>

<div class="topbar">
    <div class="container">
        <span>Mini Online Bookstore</span>
        <span>Customer profile area</span>
    </div>
</div>

<?php include __DIR__ . '/../includes/navigation.php'; ?>

<section class="page-title">
    <div class="container">
        <p class="eyebrow">Customer Account</p>
        <h1>My Profile</h1>
        <p>View your registered account information and access customer features.</p>
    </div>
</section>

<main class="section">
    <div class="container auth">
        <div class="form-card">
            <h2>Profile Information</h2>

            <div class="summary-row">
                <span>Customer ID</span>
                <strong><?php echo "C" . str_pad($user["user_id"], 4, "0", STR_PAD_LEFT); ?></strong>
            </div>

            <div class="summary-row">
                <span>Full Name</span>
                <strong><?php echo htmlspecialchars($user["name"]); ?></strong>
            </div>

            <div class="summary-row">
                <span>Email</span>
                <strong><?php echo htmlspecialchars($user["email"]); ?></strong>
            </div>

            <div class="summary-row">
                <span>Contact Number</span>
                <strong><?php echo htmlspecialchars($user["contact"]); ?></strong>
            </div>

            <div class="summary-row">
                <span>Role</span>
                <strong><?php echo htmlspecialchars(ucfirst($user["role"])); ?></strong>
            </div>

            <div class="summary-row">
                <span>Total Orders</span>
                <strong><?php echo $orderCount; ?></strong>
            </div>

            <div class="summary-row">
                <span>Registered At</span>
                <strong><?php echo htmlspecialchars($user["created_at"]); ?></strong>
            </div>

            <div class="actions2">
                <a class="btn" href="edit-profile.php">Edit Profile</a>
                <a class="btn secondary view-btn" href="../orders/order-history.php">View Orders</a>
                <a class="btn danger logout-btn" href="logout.php">Logout</a>
            </div>
        </div>
    </div>
</main>

<footer class="footer">
    <div class="container footer-grid">
        <div>
            <h3>BookNest</h3>
            <p>Mini Online Bookstore e-commerce system.</p>
        </div>
        <div>
            <h4>Customer</h4>
            <a href="../books/books.php">Browse Books</a>
            <a href="../orders/cart.php">Shopping Cart</a>
            <a href="../orders/order-history.php">Order History</a>
        </div>
        <div>
            <h4>Account</h4>
            <a href="profile.php">Profile</a>
            <a href="logout.php">Logout</a>
        </div>
    </div>
</footer>

</body>
</html>