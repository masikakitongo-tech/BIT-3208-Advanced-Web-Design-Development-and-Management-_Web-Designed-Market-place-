<?php
// ============================================================
// FILE: pages/cart.php
// PURPOSE: Shopping cart view and checkout
// ============================================================
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireLogin();
$pageTitle = 'My Cart';
$uid = (int)$_SESSION['user_id'];

// FIX: use prepared statement for cart query
$stmt = mysqli_prepare($conn,
    "SELECT c.*, p.title, p.price, p.emoji, p.category, p.status, c.quantity
     FROM cart c
     JOIN products p ON c.product_id = p.id
     WHERE c.user_id = ?
     ORDER BY c.added_at DESC");
mysqli_stmt_bind_param($stmt, 'i', $uid);
mysqli_stmt_execute($stmt);
$cartQ = mysqli_stmt_get_result($stmt);

$items    = [];
$subtotal = 0;
while ($row = mysqli_fetch_assoc($cartQ)) {
    $items[]   = $row;
    $subtotal += $row['price'] * $row['quantity'];
}
$shipping = count($items) > 0 ? 5.00 : 0;
$total    = $subtotal + $shipping;

$success = $error = '';

// Checkout handler
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['checkout'])) {
    if (count($items) === 0) {
        $error = 'Your cart is empty.';
    } else {
        // FIX: use a transaction so orders are atomic
        mysqli_begin_transaction($conn);
        try {
            $ordStmt = mysqli_prepare($conn,
                "INSERT INTO orders (buyer_id, total, status) VALUES (?, ?, 'pending')");
            mysqli_stmt_bind_param($ordStmt, 'id', $uid, $total);
            mysqli_stmt_execute($ordStmt);
            $orderId = mysqli_insert_id($conn);

            $itemStmt = mysqli_prepare($conn,
                "INSERT INTO order_items (order_id, product_id, quantity, price_each)
                 VALUES (?, ?, ?, ?)");
            $updStmt = mysqli_prepare($conn,
                "UPDATE products SET status = 'sold' WHERE id = ?");

            foreach ($items as $item) {
                $pid   = (int)$item['product_id'];
                $qty   = (int)$item['quantity'];
                $price = (float)$item['price'];

                mysqli_stmt_bind_param($itemStmt, 'iiid', $orderId, $pid, $qty, $price);
                mysqli_stmt_execute($itemStmt);

                mysqli_stmt_bind_param($updStmt, 'i', $pid);
                mysqli_stmt_execute($updStmt);
            }

            // Clear cart
            $clrStmt = mysqli_prepare($conn, "DELETE FROM cart WHERE user_id = ?");
            mysqli_stmt_bind_param($clrStmt, 'i', $uid);
            mysqli_stmt_execute($clrStmt);

            mysqli_commit($conn);
            $success = "Order #$orderId placed successfully! Thank you for shopping.";
            $items = []; $subtotal = $shipping = $total = 0;
        } catch (Exception $e) {
            mysqli_rollback($conn);
            $error = 'Checkout failed. Please try again.';
        }
    }
}

include '../includes/header.php';
?>
<p class="breadcrumb"><a href="dashboard.php">Dashboard</a> / Cart</p>
<h1 class="page-title">Shopping Cart</h1>

<?php if ($success): ?><div class="alert alert-success" style="max-width:1000px;margin:0 auto 1rem;padding:0 1.5rem"><?= htmlspecialchars($success) ?></div><?php endif; ?>
<?php if ($error):   ?><div class="alert alert-error"   style="max-width:1000px;margin:0 auto 1rem;padding:0 1.5rem"><?= htmlspecialchars($error) ?></div><?php endif; ?>

<div class="cart-layout">
    <!-- Cart items -->
    <div>
        <?php if (empty($items)): ?>
            <div class="empty-state">
                <i class="fas fa-shopping-bag"></i>
                <h3>Your cart is empty</h3>
                <p><a href="shop.php" style="color:var(--rust)">Continue shopping &rarr;</a></p>
            </div>
        <?php else: ?>
            <?php foreach ($items as $item): ?>
            <div class="cart-item">
                <div class="cart-item-img"><?= htmlspecialchars($item['emoji']) ?></div>
                <div class="cart-item-info">
                    <div class="cart-item-name"><?= htmlspecialchars($item['title']) ?></div>
                    <div class="cart-item-price">$<?= number_format($item['price'],2) ?> each</div>
                    <div class="cart-qty">
                        <form method="POST" action="cart_action.php" style="display:inline-flex;align-items:center;gap:.3rem">
                            <input type="hidden" name="product_id" value="<?= (int)$item['product_id'] ?>">
                            <input type="hidden" name="action" value="update">
                            <button class="qty-btn" type="submit" name="qty"
                                    value="<?= $item['quantity']-1 ?>">−</button>
                            <span style="min-width:24px;text-align:center"><?= (int)$item['quantity'] ?></span>
                            <button class="qty-btn" type="submit" name="qty"
                                    value="<?= $item['quantity']+1 ?>">+</button>
                        </form>
                        <form method="POST" action="cart_action.php">
                            <input type="hidden" name="product_id" value="<?= (int)$item['product_id'] ?>">
                            <input type="hidden" name="action" value="remove">
                            <button style="background:none;border:none;color:#aaa;cursor:pointer;font-size:.82rem">
                                Remove
                            </button>
                        </form>
                    </div>
                </div>
                <strong style="white-space:nowrap">
                    $<?= number_format($item['price'] * $item['quantity'],2) ?>
                </strong>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Summary -->
    <div class="cart-summary">
        <h3>Order Summary</h3>
        <div class="summary-row"><span>Subtotal</span><span>$<?= number_format($subtotal,2) ?></span></div>
        <div class="summary-row"><span>Shipping</span><span>$<?= number_format($shipping,2) ?></span></div>
        <div class="summary-row total"><span>Total</span><span>$<?= number_format($total,2) ?></span></div>
        <?php if (!empty($items)): ?>
        <form method="POST">
            <button type="submit" name="checkout" class="btn-checkout">
                <i class="fas fa-lock"></i> Place Order
            </button>
        </form>
        <?php endif; ?>
        <a href="shop.php" style="display:block;text-align:center;margin-top:.8rem;font-size:.85rem;color:var(--sand)">
            &larr; Continue Shopping
        </a>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
