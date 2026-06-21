<?php
$projectRoot = realpath(__DIR__ . '/..');
$docRoot = realpath($_SERVER['DOCUMENT_ROOT']);

$baseUrl = '/';

if ($projectRoot && $docRoot) {
    $baseUrl = str_replace('\\', '/', str_replace($docRoot, '', $projectRoot));
    $baseUrl = '/' . trim($baseUrl, '/') . '/';

    if ($baseUrl === '//') {
        $baseUrl = '/';
    }
}

function navActive($paths) {
    $current = str_replace('\\', '/', $_SERVER['SCRIPT_NAME']);

    foreach ((array)$paths as $path) {
        if (strpos($current, $path) !== false) {
            return 'active';
        }
    }

    return '';
}

$isHome = basename($_SERVER['SCRIPT_NAME']) === 'index.php';
?>

<header class="navbar">
    <div class="container nav-inner">
        <a class="brand" href="<?php echo $baseUrl; ?>index.php">Book<span>Nest</span></a>

        <nav class="nav-links">
            <a 
                class="<?php echo $isHome ? 'active' : ''; ?>" 
                href="<?php echo $baseUrl; ?>index.php"
            >
                Home
            </a>

            <a 
                class="<?php echo navActive('/books/'); ?>" 
                href="<?php echo $baseUrl; ?>books/books.php"
            >
                Books
            </a>

            <?php if (!isLoggedIn()): ?>

                <a 
                    class="<?php echo navActive('/auth/register.php'); ?>" 
                    href="<?php echo $baseUrl; ?>auth/register.php"
                >
                    Register
                </a>

                <a 
                    class="<?php echo navActive('/auth/login.php'); ?>" 
                    href="<?php echo $baseUrl; ?>auth/login.php"
                >
                    Login
                </a>

            <?php elseif (isAdmin()): ?>

                <a 
                    class="<?php echo navActive('/admin/admin-dashboard.php'); ?>" 
                    href="<?php echo $baseUrl; ?>admin/admin-dashboard.php"
                >
                    Dashboard
                </a>

                <a 
                    class="<?php echo navActive('/admin/manage-books.php'); ?>" 
                    href="<?php echo $baseUrl; ?>admin/manage-books.php"
                >
                    Manage Books
                </a>

                <a 
                    class="<?php echo navActive('/admin/manage-orders.php'); ?>" 
                    href="<?php echo $baseUrl; ?>admin/manage-orders.php"
                >
                    Manage Orders
                </a>

                <a 
                    class="<?php echo navActive('/admin/customer-messages.php'); ?>" 
                    href="<?php echo $baseUrl; ?>admin/customer-messages.php"
                >
                    Messages
                </a>

                <a href="<?php echo $baseUrl; ?>auth/logout.php">
                    Logout
                </a>

            <?php else: ?>

                <a 
                    class="<?php echo navActive(['/orders/cart.php', '/orders/checkout.php', '/orders/payment.php']); ?>" 
                    href="<?php echo $baseUrl; ?>orders/cart.php"
                >
                    Cart
                </a>

                <a 
                    class="<?php echo navActive(['/orders/order-history.php', '/orders/receipt.php']); ?>" 
                    href="<?php echo $baseUrl; ?>orders/order-history.php"
                >
                    Orders
                </a>

                <a 
                    class="<?php echo navActive('/feedback/customer-chat.php'); ?>" 
                    href="<?php echo $baseUrl; ?>feedback/customer-chat.php"
                >
                    Feedback
                </a>

                <a 
                    class="<?php echo navActive('/auth/profile.php'); ?>" 
                    href="<?php echo $baseUrl; ?>auth/profile.php"
                >
                    Profile
                </a>

                <a href="<?php echo $baseUrl; ?>auth/logout.php">
                    Logout
                </a>

            <?php endif; ?>
        </nav>
    </div>
</header>

<?php include __DIR__ . '/../chatbot/chatbot.php'; ?>