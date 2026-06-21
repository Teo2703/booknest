<?php
function adminSidebarActive($page) {
    $currentPage = basename($_SERVER['SCRIPT_NAME']);

    if ($currentPage === $page) {
        return 'active';
    }

    return '';
}
?>

<aside class="sidebar">
    <a class="<?php echo adminSidebarActive('admin-dashboard.php'); ?>" href="admin-dashboard.php">
        Dashboard
    </a>

    <a class="<?php echo adminSidebarActive('manage-books.php'); ?>" href="manage-books.php">
        Manage Books
    </a>

    <a class="<?php echo adminSidebarActive('manage-orders.php'); ?>" href="manage-orders.php">
        Manage Orders
    </a>

    <a class="<?php echo adminSidebarActive('manage-refunds.php'); ?>" href="manage-refunds.php">
        Manage Refunds
    </a>

    <a class="<?php echo adminSidebarActive('customer-messages.php'); ?>" href="customer-messages.php">
        Messages
    </a>

    <a class="<?php echo adminSidebarActive('analytics.php'); ?>" href="analytics.php">
        Analytics
    </a>

    <a href="../auth/logout.php">
        Logout
    </a>
</aside>