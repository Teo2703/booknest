<?php include 'app.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home | BookNest</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <div class="topbar">
        <div class="container">
            <span>Mini Online Bookstore</span>
            <span>Free local delivery for orders above RM80</span>
        </div>
    </div>

    <header class="navbar">
        <div class="container nav-inner">
            <a class="brand" href="index.php">Book<span>Nest</span></a>
            <nav class="nav-links">
                <a class="active" href="index.php">Home</a>
                <a href="books/books.php">Books</a>
                <a href="group.html">Group</a>

                <?php if (!isLoggedIn()): ?>
                    <a href="auth/register.php">Register</a>
                    <a href="auth/login.php">Login</a>

                <?php elseif (isAdmin()): ?>
                    <a href="admin/admin-dashboard.php">Dashboard</a>
                    <a href="auth/logout.php">Logout</a>

                <?php else: ?>
                    <a href="orders/cart.php">Cart</a>
                    <a href="orders/order-history.php">Orders</a>
                    <a href="auth/logout.php">
                        👤 <?php echo htmlspecialchars($_SESSION['user_name']); ?> | Logout
                    </a>

                <?php endif; ?>
            </nav>
        </div>
    </header>

    <section class="hero">
        <div class="container hero-grid">
            <div>
                <p class="eyebrow">Mini Online Bookstore</p>
                <h1>Find your next favourite book in one simple place.</h1>
                <p>BookNest helps customers browse books, compare details, add items to cart, and place orders through a clean web-based bookstore interface.</p>
                <div class="actions">
                    <a class="btn" href="books/books.php">Browse Books</a>
                    <?php if (!isLoggedIn()): ?>
                        <a class="btn secondary" href="auth/register.php">Create Account</a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="hero-card">
                <div class="mock-book"><span>Featured</span><strong>The Quiet Library</strong></div>
                <div class="mock-book"><span>Academic Pick</span><strong>Learning Web Apps</strong></div>
                <div class="mock-book"><span>Children</span><strong>Tiny Adventures</strong></div>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="container">
            <div class="section-head">
                <div>
                    <h2>Featured Books</h2>
                    <p>Popular books selected for this static prototype.</p>
                </div>
                <a class="btn secondary" href="books/books.php">View All</a>
            </div>
            <div class="grid grid-3">
                <article class="card">
                    <div class="book-cover">The Quiet Library</div>
                    <div class="card-body">
                        <span class="tag">Fiction</span>
                        <h3 class="book-title">The Quiet Library</h3>
                        <p class="meta">by Mira Tan</p>
                        <p class="price">RM42.90</p>
                        <a class="btn secondary" href="books/book-detail.php?id=1">View Details</a>
                    </div>
                </article>
                <article class="card">
                    <div class="book-cover blue">Learning Web Apps</div>
                    <div class="card-body">
                        <span class="tag">Academic</span>
                        <h3 class="book-title">Learning Web Apps</h3>
                        <p class="meta">by D. Kumar</p>
                        <p class="price">RM58.00</p>
                        <a class="btn secondary" href="books/book-detail.php?id=2">View Details</a>
                    </div>
                </article>
                <article class="card">
                    <div class="book-cover green">Tiny Adventures</div>
                    <div class="card-body">
                        <span class="tag">Children</span>
                        <h3 class="book-title">Tiny Adventures</h3>
                        <p class="meta">by Lily Chen</p>
                        <p class="price">RM26.50</p>
                        <a class="btn secondary" href="books/book-detail.php?id=3">View Details</a>
                    </div>
                </article>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="container grid grid-4">
            <div class="card">
                <div class="card-body">
                    <h3>Easy Search</h3>
                    <p class="meta">Search books by title, author, or category.</p>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <h3>Shopping Cart</h3>
                    <p class="meta">Review selected books before checkout.</p>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <h3>Order Tracking</h3>
                    <p class="meta">Customers can view order history and status.</p>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <h3>Admin Control</h3>
                    <p class="meta">Admin can manage books and orders.</p>
                </div>
            </div>
        </div>
    </section>

    <footer class="footer">
        <div class="container footer-grid">
            <div>
                <h3>BookNest</h3>
                <p>A clean static prototype for the Mini Online Bookstore e-commerce system.</p>
            </div>
            <div>
                <h4>Customer</h4>
                <a href="books/books.php">Browse Books</a>
                <a href="orders/cart.php">Shopping Cart</a>
                <a href="orders/checkout.php">Checkout</a>
            </div>
            <div>
                <h4>Admin</h4>
                <a href="admin/admin-dashboard.php">Dashboard</a>
                <a href="admin/manage-books.php">Manage Books</a>
                <a href="admin/manage-orders.php">Manage Orders</a>
            </div>
        </div>
    </footer>

</body>
</html>