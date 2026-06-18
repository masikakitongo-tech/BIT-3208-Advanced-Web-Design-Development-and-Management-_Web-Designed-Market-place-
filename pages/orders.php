<?php
// ============================================================
// FILE: pages/orders.php
// PURPOSE: View all orders placed by logged-in user
// ============================================================
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireLogin();
$pageTitle = 'My Orders';
$uid = (int)$_SESSION['user_id'];

// FIX: prepared statement
$stmt = mysqli_prepare($conn,
    "SELECT o.*, GROUP_CONCAT(p.title SEPARATOR ', ') as items_list
     FROM orders o
     LEFT JOIN order_items oi ON o.id = oi.order_id
     LEFT JOIN products p     ON oi.product_id = p.id
     WHERE o.buyer_id = ?
     GROUP BY o.id
     ORDER BY o.created_at DESC");
mysqli_stmt_bind_param($stmt, 'i', $uid);
mysqli_stmt_execute($stmt);
$orders = mysqli_stmt_get_result($stmt);

include '../includes/header.php';
?>
<p class="breadcrumb"><a href="dashboard.php">Dashboard</a> / Orders</p>
<h1 class="page-title">My Orders</h1>

<div style="max-width:900px;margin:1.5rem auto;padding:0 1.5rem">
    <?php if (mysqli_num_rows($orders) === 0): ?>
        <div class="empty-state">
            <i class="fas fa-box-open"></i>
            <h3>No orders yet</h3>
            <p><a href="shop.php" style="color:var(--rust)">Start shopping &rarr;</a></p>
        </div>
    <?php else: ?>
        <?php while ($o = mysqli_fetch_assoc($orders)): ?>
        <div style="background:var(--white);border-radius:var(--radius-lg);padding:1.3rem 1.5rem;margin-bottom:1rem;box-shadow:0 2px 12px var(--shadow)">
            <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:.5rem">
                <div>
                    <strong style="font-family:var(--font-display)">Order #<?= (int)$o['id'] ?></strong>
                    <span style="color:#888;font-size:.82rem;margin-left:.5rem">
                        <?= date('F j, Y · g:i A', strtotime($o['created_at'])) ?>
                    </span>
                </div>
                <div style="display:flex;gap:1rem;align-items:center">
                    <strong>$<?= number_format($o['total'],2) ?></strong>
                    <span style="background:var(--mist);padding:.2rem .7rem;border-radius:99px;font-size:.75rem;font-weight:600;text-transform:capitalize">
                        <?= htmlspecialchars($o['status']) ?>
                    </span>
                </div>
            </div>
            <p style="font-size:.85rem;color:#888;margin-top:.5rem">
                Items: <?= htmlspecialchars($o['items_list'] ?? 'N/A') ?>
            </p>
        </div>
        <?php endwhile; ?>
    <?php endif; ?>
</div>
<?php include '../includes/footer.php'; ?>
