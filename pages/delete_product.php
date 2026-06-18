<?php
// ============================================================
// FILE: pages/delete_product.php
// PURPOSE: Delete a product listing (owner only)
// ============================================================
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireLogin();

// FIX: require POST (not GET) to prevent CSRF via link/image
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: dashboard.php');
    exit();
}

$uid = (int)$_SESSION['user_id'];
$pid = (int)($_POST['id'] ?? 0);

if ($pid > 0) {
    // FIX: prepared statement; seller_id check ensures users can only delete their own items
    $stmt = mysqli_prepare($conn,
        "DELETE FROM products WHERE id = ? AND seller_id = ?");
    mysqli_stmt_bind_param($stmt, 'ii', $pid, $uid);
    mysqli_stmt_execute($stmt);
}

header('Location: my_listings.php');
exit();
