<?php
include '../app.php';

$errors = [];
$email = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";
    $role = $_POST["role"] ?? "";

    if ($email === "") {
        $errors[] = "Email is required.";
    }

    if ($password === "") {
        $errors[] = "Password is required.";
    }

    if ($role !== "customer" && $role !== "admin") {
        $errors[] = "Please select a valid role.";
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT user_id, name, email, password, role FROM users WHERE email = ? AND role = ?");
        $stmt->bind_param("ss", $email, $role);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            $password_ok = password_verify($password, $user["password"]);

            /*
              This fallback supports old sample accounts in booknest.sql
              because the sample passwords are currently stored as plain text.
              When login succeeds, it updates the old password into hashed password.
            */
            if (!$password_ok && $password === $user["password"]) {
                $password_ok = true;

                $new_hash = password_hash($password, PASSWORD_DEFAULT);
                $update = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
                $update->bind_param("si", $new_hash, $user["user_id"]);
                $update->execute();
            }

            if ($password_ok) {
                $_SESSION["user_id"] = $user["user_id"];
                $_SESSION["user_name"] = $user["name"];
                $_SESSION["user_email"] = $user["email"];
                $_SESSION["user_role"] = $user["role"];

                if ($user["role"] === "admin") {
                    header("Location: ../admin/admin-dashboard.php");
                } else {
                    header("Location: profile.php");
                }
                exit();
            } else {
                $errors[] = "Invalid email, password, or role.";
            }
        } else {
            $errors[] = "Invalid email, password, or role.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | BookNest</title>
    <link rel="stylesheet" href="../css/style.css?v=123">
</head>
<body>

<div class="topbar">
    <div class="container">
        <span>Mini Online Bookstore</span>
        <span>Customer and admin login</span>
    </div>
</div>

<header class="navbar">
    <div class="container nav-inner">
        <a class="brand" href="../index.php">Book<span>Nest</span></a>
        <nav class="nav-links">
            <a href="../index.php">Home</a>
            <a href="../books/books.php">Books</a>
            <a href="register.php">Register</a>
            <a class="active" href="login.php">Login</a>
        </nav>
    </div>
</header>

<section class="page-title">
    <div class="container">
        <p class="eyebrow">Account Access</p>
        <h1>Login</h1>
        <p>Login as a customer or administrator based on your account role.</p>
    </div>
</section>

<main class="section">
    <div class="container auth">
        <form id="loginForm" class="form-card" method="POST" action="login.php" novalidate>
            <h2>Login to BookNest</h2>

            <?php if (isset($_SESSION["success"])): ?>
                <div class="notice success">
                    <p><?php echo htmlspecialchars($_SESSION["success"]); ?></p>
                </div>
                <?php unset($_SESSION["success"]); ?>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
                <div class="notice">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="field">
                <label>Email</label>
                <input class="input" type="email" name="email" value="<?php echo htmlspecialchars($email); ?>" placeholder="Enter email">
            </div>

            <div class="field">
                <label>Password</label>
                <input class="input" type="password" name="password" placeholder="Enter password">
            </div>

            <div class="field">
                <label>Role</label>
                <select name="role">
                    <option value="customer">Customer</option>
                    <option value="admin">Administrator</option>
                </select>
            </div>

            <button class="btn login-btn" type="submit">Login</button>
            <a class="btn secondary create-btn" href="register.php">Create New Account</a>

            <p class="small">
                Sample customer: amanda@example.com / 123456<br>
                Sample admin: admin@booknest.com / 123456
            </p>
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
            <a href="register.php">Register</a>
            <a href="login.php">Login</a>
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