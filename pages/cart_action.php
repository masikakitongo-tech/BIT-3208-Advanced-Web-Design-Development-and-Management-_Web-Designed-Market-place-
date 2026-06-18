<?php
// ============================================================
// FILE: pages/cart_action.php
// PURPOSE: Add / remove / update items in the cart
// ============================================================
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireLogin();

$uid    = (int)$_SESSION['user_id'];
// FIX: only accept action from POST to prevent CSRF via GET
$action = $_POST['action'] ?? '';
$pid    = (int)($_POST['product_id'] ?? 0);

if ($action === 'add' && $pid > 0) {
    // Check product exists, is available, and is not the user's own listing
    $stmt = mysqli_prepare($conn,
        "SELECT id, seller_id FROM products WHERE id = ? AND status = 'available' LIMIT 1");
    mysqli_stmt_bind_param($stmt, 'i', $pid);
    mysqli_stmt_execute($stmt);
    $prod = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    if ($prod && $prod['seller_id'] != $uid) {
        // Already in cart? Increase qty, else insert
        $chk = mysqli_prepare($conn,
            "SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ? LIMIT 1");
        mysqli_stmt_bind_param($chk, 'ii', $uid, $pid);
        mysqli_stmt_execute($chk);
        $row = mysqli_fetch_assoc(mysqli_stmt_get_result($chk));

        if ($row) {
            $newQty = $row['quantity'] + 1;
            $upd    = mysqli_prepare($conn, "UPDATE cart SET quantity = ? WHERE id = ?");
            mysqli_stmt_bind_param($upd, 'ii', $newQty, $row['id']);
            mysqli_stmt_execute($upd);
        } else {
            $ins = mysqli_prepare($conn,
                "INSERT INTO cart (user_id, product_id) VALUES (?, ?)");
            mysqli_stmt_bind_param($ins, 'ii', $uid, $pid);
            mysqli_stmt_execute($ins);
        }
    }
}

if ($action === 'remove' && $pid > 0) {
    $del = mysqli_prepare($conn,
        "DELETE FROM cart WHERE user_id = ? AND product_id = ?");
    mysqli_stmt_bind_param($del, 'ii', $uid, $pid);
    mysqli_stmt_execute($del);
}

if ($action === 'update' && $pid > 0) {
    $qty = (int)($_POST['qty'] ?? 1);
    if ($qty < 1) {
        $del = mysqli_prepare($conn,
            "DELETE FROM cart WHERE user_id = ? AND product_id = ?");
        mysqli_stmt_bind_param($del, 'ii', $uid, $pid);
        mysqli_stmt_execute($del);
    } else {
        $upd = mysqli_prepare($conn,
            "UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
        mysqli_stmt_bind_param($upd, 'iii', $qty, $uid, $pid);
        mysqli_stmt_execute($upd);
    }
}

// FIX: validate referer to prevent open redirect
$ref = $_SERVER['HTTP_REFERER'] ?? '';
$allowed = ['cart.php','shop.php','index.php'];
$safe = false;
foreach ($allowed as $page) {
    if (str_ends_with(parse_url($ref, PHP_URL_PATH) ?? '', $page)) {
        $safe = true;
        break;
    }
}
header('Location: ' . ($safe ? $ref : 'shop.php'));
exit();
