<?php
// FILE: pages/my_listings.php
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireLogin();
$pageTitle = 'My Listings';
$uid = (int)$_SESSION['user_id'];

// FIX: prepared statement
$stmt = mysqli_prepare($conn,
    "SELECT * FROM products WHERE seller_id = ? ORDER BY created_at DESC");
mysqli_stmt_bind_param($stmt, 'i', $uid);
mysqli_stmt_execute($stmt);
$products = mysqli_stmt_get_result($stmt);

include '../includes/header.php';
?>
<p class="breadcrumb"><a href="dashboard.php">Dashboard</a> / My Listings</p>
<h1 class="page-title">My Listings</h1>
<div style="max-width:1100px;margin:1.5rem auto;padding:0 1.5rem">
    <div style="margin-bottom:1.5rem">
        <a href="sell.php" class="btn-primary"><i class="fas fa-plus"></i> New Listing</a>
    </div>
    <?php if (mysqli_num_rows($products) === 0): ?>
        <div class="empty-state">
            <i class="fas fa-tags"></i>
            <h3>No listings yet</h3>
            <p><a href="sell.php" style="color:var(--rust)">List your first item &rarr;</a></p>
        </div>
    <?php else: ?>
        <div class="product-grid">
            <?php while ($p = mysqli_fetch_assoc($products)): ?>
            <div class="product-card">
                <div class="product-img"><?= htmlspecialchars($p['emoji']) ?></div>
                <div class="product-info">
                    <p class="product-category"><?= ucfirst(htmlspecialchars($p['category'])) ?></p>
                    <h3 class="product-name"><?= htmlspecialchars($p['title']) ?></h3>
                    <p class="product-price">$<?= number_format($p['price'],2) ?></p>
                    <p style="font-size:.8rem;margin-bottom:.8rem">
                        Status: <strong><?= ucfirst(htmlspecialchars($p['status'])) ?></strong>
                    </p>
                    <!-- FIX: POST form instead of raw GET link -->
                    <form method="POST" action="delete_product.php"
                          onsubmit="return confirm('Delete this listing?')"
                          style="display:inline">
                        <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                        <button type="submit"
                                style="background:none;border:none;color:var(--rust);font-size:.85rem;font-weight:600;cursor:pointer;padding:0">
                            🗑 Delete Listing
                        </button>
                    </form>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    <?php endif; ?>
</div>
<?php include '../includes/footer.php'; ?>
