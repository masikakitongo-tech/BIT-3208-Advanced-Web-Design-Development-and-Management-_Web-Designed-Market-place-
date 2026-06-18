<?php
// ============================================================
// FILE: includes/auth.php
// PURPOSE: Session management and authentication helpers
// ============================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect to login if not authenticated
function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        // FIX: use absolute path so this works regardless of which
        // subdirectory the calling script lives in
        $base = dirname(dirname($_SERVER['SCRIPT_NAME']));
        header('Location: ' . rtrim($base, '/') . '/pages/login.php');
        exit();
    }
}

// Redirect to dashboard if already logged in
function redirectIfLoggedIn() {
    if (isset($_SESSION['user_id'])) {
        $base = dirname($_SERVER['SCRIPT_NAME']);
        header('Location: ' . rtrim($base, '/') . '/dashboard.php');
        exit();
    }
}

// Get current logged-in user info
function getCurrentUser($conn) {
    if (!isset($_SESSION['user_id'])) return null;
    $id = (int)$_SESSION['user_id'];
    // FIX: use prepared statement to prevent SQL injection
    $stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE id = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_assoc($result);
}

// Get cart item count for the logged-in user
function getCartCount($conn) {
    if (!isset($_SESSION['user_id'])) return 0;
    $id = (int)$_SESSION['user_id'];
    $result = mysqli_query($conn, "SELECT SUM(quantity) as total FROM cart WHERE user_id = $id");
    $row = mysqli_fetch_assoc($result);
    return (int)($row['total'] ?? 0);
}
