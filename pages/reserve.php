<?php
// ============================================================
// FILE: pages/reserve.php
// PURPOSE: Reserve a product for 24 hours
// ============================================================
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireLogin();

// FIX: only accept POST to prevent accidental/bot GET triggers
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: shop.php');
    exit();
}

$uid = (int)$_SESSION['user_id'];
$pid = (int)($_POST['product_id'] ?? 0);

if ($pid > 0) {
    // FIX: use prepared statement and also check the product isn't already reserved by someone else
    $stmt = mysqli_prepare($conn,
        "SELECT id, seller_id, status FROM products WHERE id = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, 'i', $pid);
    mysqli_stmt_execute($stmt);
    $prod = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    if ($prod && $prod['status'] === 'available' && $prod['seller_id'] != $uid) {
        // Remove any old reservation by this user on this product
        $del = mysqli_prepare($conn,
            "DELETE FROM reservations WHERE user_id = ? AND product_id = ?");
        mysqli_stmt_bind_param($del, 'ii', $uid, $pid);
        mysqli_stmt_execute($del);

        // Insert new 24h reservation
        $ins = mysqli_prepare($conn,
            "INSERT INTO reservations (user_id, product_id) VALUES (?, ?)");
        mysqli_stmt_bind_param($ins, 'ii', $uid, $pid);
        mysqli_stmt_execute($ins);

        // Mark product reserved
        $upd = mysqli_prepare($conn,
            "UPDATE products SET status = 'reserved' WHERE id = ?");
        mysqli_stmt_bind_param($upd, 'i', $pid);
        mysqli_stmt_execute($upd);
    }
}

// FIX: validate referer
$ref = $_SERVER['HTTP_REFERER'] ?? '';
$allowed = ['shop.php','index.php','dashboard.php'];
$safe = false;
foreach ($allowed as $page) {
    if (str_ends_with(parse_url($ref, PHP_URL_PATH) ?? '', $page)) {
        $safe = true;
        break;
    }
}
header('Location: ' . ($safe ? $ref : 'shop.php'));
exit();
