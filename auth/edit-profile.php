<?php
include '../app.php';

requireCustomer();

$user_id = (int)$_SESSION["user_id"];

$stmt = $conn->prepare("SELECT user_id, name, email, contact, role FROM users WHERE user_id = ?");
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

$error = "";
$success = "";

$name = $user["name"];
$email = $user["email"];
$contact = $user["contact"];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $contact = trim($_POST["contact"] ?? "");
    $newPassword = trim($_POST["new_password"] ?? "");
    $confirmPassword = trim($_POST["confirm_password"] ?? "");

    if ($name === "" || $email === "" || $contact === "") {
        $error = "Name, email and contact number are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } elseif (!preg_match("/^[0-9]{10,15}$/", $contact)) {
        $error = "Contact number must contain 10 to 15 digits only.";
    } elseif ($newPassword !== "" && strlen($newPassword) < 6) {
        $error = "New password must be at least 6 characters.";
    } elseif ($newPassword !== $confirmPassword) {
        $error = "Password confirmation does not match.";
    } else {
        $checkStmt = $conn->prepare("SELECT user_id FROM users WHERE email = ? AND user_id != ?");
        $checkStmt->bind_param("si", $email, $user_id);
        $checkStmt->execute();
        $existingEmail = $checkStmt->get_result()->fetch_assoc();

        if ($existingEmail) {
            $error = "This email is already used by another account.";
        } else {
            if ($newPassword !== "") {
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

                $updateStmt = $conn->prepare("
                    UPDATE users 
                    SET name = ?, email = ?, contact = ?, password = ?
                    WHERE user_id = ?
                ");
                $updateStmt->bind_param("ssssi", $name, $email, $contact, $hashedPassword, $user_id);
            } else {
                $updateStmt = $conn->prepare("
                    UPDATE users 
                    SET name = ?, email = ?, contact = ?
                    WHERE user_id = ?
                ");
                $updateStmt->bind_param("sssi", $name, $email, $contact, $user_id);
            }

            if ($updateStmt->execute()) {
                $_SESSION["name"] = $name;

                header("Location: profile.php?updated=1");
                exit();
            } else {
                $error = "Unable to update profile. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile | BookNest</title>
    <link rel="stylesheet" href="../css/style.css?v=<?php echo filemtime(__DIR__ . '/../css/style.css'); ?>">
</head>
<body>

<div class="topbar">
    <div class="container">
        <span>Mini Online Bookstore</span>
        <span>Edit customer profile</span>
    </div>
</div>

<?php include __DIR__ . '/../includes/navigation.php'; ?>

<section class="page-title">
    <div class="container">
        <p class="eyebrow">Customer Account</p>
        <h1>Edit Profile</h1>
        <p>Update your account information and change password if needed.</p>
    </div>
</section>

<main class="section">
    <div class="container auth">
        <form class="form-card" method="POST" novalidate>
            <h2>Edit Profile</h2>

            <?php if ($error !== ""): ?>
                <div class="notice" style="color:#c0392b;margin-bottom:1rem;">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if ($success !== ""): ?>
                <div class="notice" style="color:#216241;margin-bottom:1rem;">
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <div class="field">
                <label>Full Name</label>
                <input 
                    class="input" 
                    type="text" 
                    name="name" 
                    value="<?php echo htmlspecialchars($name); ?>" 
                    required
                >
            </div>

            <div class="field">
                <label>Email</label>
                <input 
                    class="input" 
                    type="email" 
                    name="email" 
                    value="<?php echo htmlspecialchars($email); ?>" 
                    required
                >
            </div>

            <div class="field">
                <label>Contact Number</label>
                <input 
                    class="input" 
                    type="text" 
                    name="contact" 
                    value="<?php echo htmlspecialchars($contact); ?>" 
                    required
                >
            </div>

            <hr style="border:0;border-top:1px solid var(--line);margin:1.2rem 0;">

            <p class="small">Leave password fields empty if you do not want to change password.</p>

            <div class="field">
                <label>New Password</label>
                <input 
                    class="input" 
                    type="password" 
                    name="new_password" 
                    placeholder="Enter new password"
                >
            </div>

            <div class="field">
                <label>Confirm New Password</label>
                <input 
                    class="input" 
                    type="password" 
                    name="confirm_password" 
                    placeholder="Confirm new password"
                >
            </div>

            <div class="actions2">
                <button class="btn" type="submit">Save Changes</button>
                <a class="btn secondary view-btn" href="profile.php">Back</a>
            </div>
        </form>
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