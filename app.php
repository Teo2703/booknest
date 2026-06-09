<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once __DIR__ . '/db.php';

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

function isCustomer() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'customer';
}

function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: /booknest/auth/login.php");
        exit();
    }
}

function requireAdmin() {
    requireLogin();

    if (!isAdmin()) {
        header("Location: /booknest/auth/login.php");
        exit();
    }
}

function requireCustomer() {
    requireLogin();

    if (!isCustomer()) {
        header("Location: /booknest/auth/login.php");
        exit();
    }
}

function isActive($page) {
    return strpos($_SERVER['REQUEST_URI'], $page) !== false ? 'active' : '';
}
?>