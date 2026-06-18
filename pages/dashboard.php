<?php
// ============================================================
// FILE: pages/dashboard.php
// PURPOSE: Seller/buyer dashboard after login
// ============================================================
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireLogin();
$pageTitle = 'Dashboard';
$user = getCurrentUser($conn);
$uid  = (int)$_SESSION['user_id'];

// FIX: if getCurrentUser returns null (corrupted session) force logout
if (!$user) {
    session_destroy();
    header('Location: login.php');
    exit();
}

// Stats — direct integer user ID in queries is safe
$myListings = (int)mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COUNT(*) c FROM products WHERE seller_id=$uid AND status!='sold'"))['c'];
$mySold     = (int)mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COUNT(*) c FROM products WHERE seller_id=$uid AND status='sold'"))['c'];
$cartItems  = getCartCount($conn);
$myOrders   = (int)mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COUNT(*) c FROM orders WHERE buyer_id=$uid"))['c'];
// FIX: guard against MySQL versions that don't support CURRENT_TIMESTAMP in DEFAULT for expires_at
$reserved   = (int)mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COUNT(*) c FROM reservations WHERE user_id=$uid AND expires_at > NOW()"))['c'];

// Recent listings
$myProducts = mysqli_query($conn,
    "SELECT * FROM products WHERE seller_id=$uid ORDER BY created_at DESC LIMIT 5");

// Recent orders
$myOrdersQ = mysqli_query($conn,
    "SELECT o.*, COUNT(oi.id) as items
     FROM orders o
     LEFT JOIN order_items oi ON o.id=oi.order_id
     WHERE o.buyer_id=$uid
     GROUP BY o.id
     ORDER BY o.created_at DESC LIMIT 5");

include '../includes/header.php';
$firstLetter = strtoupper(mb_substr($user['full_name'], 0, 1));
?>

<div class="dashboard-layout">
    <!-- Sidebar -->
    <aside class="dash-sidebar">
        <div class="dash-avatar"><?= htmlspecialchars($firstLetter) ?></div>
        <div class="dash-username"><?= htmlspecialchars($user['full_name']) ?></div>
        <div class="dash-role">Member since <?= date('M Y', strtotime($user['created_at'])) ?></div>
        <nav class="dash-nav">
            <a href="dashboard.php" class="active"><i class="fas fa-home"></i> Overview</a>
            <a href="shop.php"><i class="fas fa-store"></i> Browse Shop</a>
            <a href="sell.php"><i class="fas fa-plus-circle"></i> List Item</a>
            <a href="cart.php"><i class="fas fa-shopping-bag"></i> My Cart
                <?php if ($cartItems > 0): ?>
                    <span class="cart-badge" style="position:static;margin-left:auto"><?= $cartItems ?></span>
                <?php endif; ?>
            </a>
            <a href="orders.php"><i class="fas fa-box"></i> My Orders</a>
            <a href="my_listings.php"><i class="fas fa-tags"></i> My Listings</a>
            <a href="logout.php" style="margin-top:1rem"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </nav>
    </aside>

    <!-- Main -->
    <main class="dash-main">
        <div class="dash-greeting">
            <h1>Hey, <?= htmlspecialchars(explode(' ', $user['full_name'])[0]) ?> 👋</h1>
            <p><?= date('l, F j, Y') ?> — Here's your marketplace overview.</p>
        </div>

        <!-- Stats -->
        <div class="stats-grid">
            <div class="stat-card accent">
                <div class="stat-icon">🏷️</div>
                <div class="stat-value"><?= $myListings ?></div>
                <div class="stat-label">Active Listings</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">✅</div>
                <div class="stat-value"><?= $mySold ?></div>
                <div class="stat-label">Items Sold</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">🛒</div>
                <div class="stat-value"><?= $cartItems ?></div>
                <div class="stat-label">Cart Items</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">📦</div>
                <div class="stat-value"><?= $myOrders ?></div>
                <div class="stat-label">Orders Placed</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">📌</div>
                <div class="stat-value"><?= $reserved ?></div>
                <div class="stat-label">Reserved</div>
            </div>
        </div>

        <!-- Quick actions -->
        <div class="dash-section-title">Quick Actions</div>
        <div style="display:flex;gap:1rem;flex-wrap:wrap;margin-bottom:2.5rem">
            <a href="sell.php" class="btn-primary"><i class="fas fa-plus"></i> List New Item</a>
            <a href="shop.php" class="btn-secondary" style="color:var(--ink);border-color:#ccc">
                <i class="fas fa-store"></i> Browse Shop
            </a>
            <a href="cart.php" class="btn-secondary" style="color:var(--ink);border-color:#ccc">
                <i class="fas fa-shopping-bag"></i> View Cart
            </a>
        </div>

        <!-- My recent listings -->
        <div class="dash-section-title">My Recent Listings</div>
        <?php if (mysqli_num_rows($myProducts) === 0): ?>
            <div class="empty-state">
                <i class="fas fa-tags"></i>
                <h3>No listings yet</h3>
                <p><a href="sell.php" style="color:var(--rust)">List your first item &rarr;</a></p>
            </div>
        <?php else: ?>
            <div style="overflow-x:auto;margin-bottom:2.5rem">
                <table style="width:100%;border-collapse:collapse;font-size:.9rem">
                    <thead>
                        <tr style="border-bottom:2px solid var(--mist);text-align:left">
                            <th style="padding:.6rem .5rem">Item</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($p = mysqli_fetch_assoc($myProducts)): ?>
                        <tr style="border-bottom:1px solid var(--mist)">
                            <td style="padding:.7rem .5rem">
                                <?= htmlspecialchars($p['emoji']) ?> <?= htmlspecialchars($p['title']) ?>
                            </td>
                            <td><?= ucfirst(htmlspecialchars($p['category'])) ?></td>
                            <td><strong>$<?= number_format($p['price'],2) ?></strong></td>
                            <td>
                                <span style="
                                    padding:.15rem .55rem;
                                    border-radius:99px;
                                    font-size:.75rem;
                                    font-weight:600;
                                    background:<?= $p['status']==='available'?'#D1FAE5':($p['status']==='reserved'?'#FEF3C7':'#FEE2E2') ?>;
                                    color:<?= $p['status']==='available'?'#065F46':($p['status']==='reserved'?'#92400E':'#991B1B') ?>
                                ">
                                    <?= ucfirst(htmlspecialchars($p['status'])) ?>
                                </span>
                            </td>
                            <td>
                                <!-- FIX: delete now uses POST form (not raw link) to prevent CSRF -->
                                <form method="POST" action="delete_product.php"
                                      onsubmit="return confirm('Delete this listing?')"
                                      style="display:inline">
                                    <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                                    <button type="submit"
                                            style="background:none;border:none;color:var(--rust);font-size:.82rem;cursor:pointer;padding:0">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <!-- Recent orders -->
        <div class="dash-section-title">Recent Orders</div>
        <?php if (mysqli_num_rows($myOrdersQ) === 0): ?>
            <div class="empty-state">
                <i class="fas fa-box-open"></i>
                <h3>No orders yet</h3>
                <p><a href="shop.php" style="color:var(--rust)">Start shopping &rarr;</a></p>
            </div>
        <?php else: ?>
            <?php while ($o = mysqli_fetch_assoc($myOrdersQ)): ?>
            <div style="background:var(--white);border-radius:var(--radius);padding:1rem 1.2rem;margin-bottom:.8rem;box-shadow:0 2px 8px var(--shadow);display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:.5rem">
                <div>
                    <strong>Order #<?= (int)$o['id'] ?></strong>
                    <span style="color:#888;font-size:.8rem;margin-left:.5rem"><?= date('M j, Y', strtotime($o['created_at'])) ?></span>
                </div>
                <div style="display:flex;gap:1rem;align-items:center">
                    <span style="font-weight:700">$<?= number_format($o['total'],2) ?></span>
                    <span style="background:var(--mist);padding:.2rem .6rem;border-radius:99px;font-size:.75rem;font-weight:600">
                        <?= ucfirst(htmlspecialchars($o['status'])) ?>
                    </span>
                </div>
            </div>
            <?php endwhile; ?>
        <?php endif; ?>
    </main>
</div>

<?php include '../includes/footer.php'; ?>
