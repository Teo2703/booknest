<?php
include '../app.php';

$errors = [];
$name = "";
$email = "";
$contact = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $contact = trim($_POST["contact"] ?? "");
    $password = $_POST["password"] ?? "";
    $confirm_password = $_POST["confirm_password"] ?? "";

    if ($name === "") {
        $errors[] = "Full name is required.";
    }

    if ($email === "") {
        $errors[] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    }

    if ($contact === "") {
        $errors[] = "Contact number is required.";
    } elseif (!preg_match("/^[0-9]{10,12}$/", $contact)) {
        $errors[] = "Contact number must be 10 to 12 digits.";
    }

    if ($password === "") {
        $errors[] = "Password is required.";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters.";
    }

    if ($confirm_password === "") {
        $errors[] = "Confirm password is required.";
    } elseif ($password !== $confirm_password) {
        $errors[] = "Password and confirm password do not match.";
    }

    if (empty($errors)) {
        $check = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows > 0) {
            $errors[] = "This email is already registered.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $role = "customer";

            $stmt = $conn->prepare("INSERT INTO users (name, email, password, role, contact) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $name, $email, $hashed_password, $role, $contact);

            if ($stmt->execute()) {
                $_SESSION["success"] = "Registration successful. Please login.";
                header("Location: login.php");
                exit();
            } else {
                $errors[] = "Registration failed. Please try again.";
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
    <title>Register | BookNest</title>
    <link rel="stylesheet" href="../css/style.css?v=123">
</head>
<body>

<div class="topbar">
    <div class="container">
        <span>Mini Online Bookstore</span>
        <span>Create your customer account</span>
    </div>
</div>

<header class="navbar">
    <div class="container nav-inner">
        <a class="brand" href="../index.php">Book<span>Nest</span></a>
        <nav class="nav-links">
            <a href="../index.php">Home</a>
            <a href="../books/books.php">Books</a>
            <a class="active" href="register.php">Register</a>
            <a href="login.php">Login</a>
        </nav>
    </div>
</header>

<section class="page-title">
    <div class="container">
        <p class="eyebrow">New Customer</p>
        <h1>Register Account</h1>
        <p>Create an account to place book orders and view your order history.</p>
    </div>
</section>

<main class="section">
    <div class="container auth">
        <form id="registerForm" class="form-card" method="POST" action="register.php" novalidate>
            <h2>Customer Registration</h2>

            <?php if (!empty($errors)): ?>
                <div class="notice">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="field">
                <label>Full Name</label>
                <input class="input" type="text" name="name" value="<?php echo htmlspecialchars($name); ?>" placeholder="Enter full name" required>
            </div>

            <div class="field">
                <label>Email</label>
                <input class="input" type="email" name="email" value="<?php echo htmlspecialchars($email); ?>" placeholder="Enter email" required>
            </div>

            <div class="field">
                <label>Contact Number</label>
                <input class="input" type="text" name="contact" value="<?php echo htmlspecialchars($contact); ?>" placeholder="Example: 0123456789" required>
            </div>

            <div class="field">
                <label>Password</label>
                <input class="input" type="password" name="password" placeholder="Minimum 6 characters" required>
            </div>

            <div class="field">
                <label>Confirm Password</label>
                <input class="input" type="password" name="confirm_password" placeholder="Re-enter password" required>
            </div>

            <button class="btn register-btn" type="submit">Register</button>
            <a class="btn secondary already-btn" href="login.php">Already have an account?</a>
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
            <a href="login.php">Login</a>
            <a href="register.php">Register</a>
        </div>
        <div>
            <h4>Admin</h4>
            <a href="../admin/admin-dashboard.php">Dashboard</a>
            <a href="../admin/manage-books.php">Manage Books</a>
            <a href="../admin/manage-orders.php">Manage Orders</a>
        </div>
    </div>
</footer>
<script src="../js/validation.js"></script>
</body>
</html>