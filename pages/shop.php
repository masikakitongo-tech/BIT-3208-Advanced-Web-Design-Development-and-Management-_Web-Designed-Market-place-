<?php
// ============================================================
// FILE: pages/shop.php
// PURPOSE: Product listing with search and category filter
// ============================================================
require_once '../includes/db.php';
require_once '../includes/auth.php';
$pageTitle = 'Shop';

$allowedCats = ['all','sweaters','hoodies','pants','jorts','other'];
$cat    = $_GET['cat'] ?? 'all';
if (!in_array($cat, $allowedCats)) $cat = 'all';

// FIX: use prepared statement with LIKE for search
$search = trim($_GET['q'] ?? '');

// Build query safely
if ($cat !== 'all' && $search !== '') {
    $like = "%$search%";
    $stmt = mysqli_prepare($conn,
        "SELECT p.*, u.full_name as seller
         FROM products p JOIN users u ON p.seller_id = u.id
         WHERE p.status = 'available' AND p.category = ?
           AND (p.title LIKE ? OR p.description LIKE ?)
         ORDER BY p.created_at DESC");
    mysqli_stmt_bind_param($stmt, 'sss', $cat, $like, $like);
} elseif ($cat !== 'all') {
    $stmt = mysqli_prepare($conn,
        "SELECT p.*, u.full_name as seller
         FROM products p JOIN users u ON p.seller_id = u.id
         WHERE p.status = 'available' AND p.category = ?
         ORDER BY p.created_at DESC");
    mysqli_stmt_bind_param($stmt, 's', $cat);
} elseif ($search !== '') {
    $like = "%$search%";
    $stmt = mysqli_prepare($conn,
        "SELECT p.*, u.full_name as seller
         FROM products p JOIN users u ON p.seller_id = u.id
         WHERE p.status = 'available'
           AND (p.title LIKE ? OR p.description LIKE ?)
         ORDER BY p.created_at DESC");
    mysqli_stmt_bind_param($stmt, 'ss', $like, $like);
} else {
    $stmt = mysqli_prepare($conn,
        "SELECT p.*, u.full_name as seller
         FROM products p JOIN users u ON p.seller_id = u.id
         WHERE p.status = 'available'
         ORDER BY p.created_at DESC");
}
mysqli_stmt_execute($stmt);
$products = mysqli_stmt_get_result($stmt);

$categories = ['all','sweaters','hoodies','pants','jorts','other'];
$catEmoji   = ['all'=>'🛍️','sweaters'=>'🧶','hoodies'=>'🧥','pants'=>'👖','jorts'=>'🩳','other'=>'✨'];

include '../includes/header.php';
?>
<div class="shop-page">
    <div class="shop-header">
        <h1>The Shop</h1>
        <p style="color:#666;margin:.3rem 0 1.2rem">Browse fashion from real people in your community.</p>

        <form method="GET" class="search-bar">
            <input type="hidden" name="cat" value="<?= htmlspecialchars($cat) ?>">
            <input type="text" name="q"
                   placeholder="Search hoodies, jeans, sweaters..."
                   value="<?= htmlspecialchars($search) ?>">
            <button type="submit"><i class="fas fa-search"></i></button>
        </form>
    </div>

    <!-- Category pills -->
    <div class="category-bar">
        <?php foreach ($categories as $c): ?>
        <button
            class="cat-pill <?= ($cat === $c || ($cat === '' && $c === 'all')) ? 'active' : '' ?>"
            data-cat="<?= $c ?>">
            <?= $catEmoji[$c] ?> <?= ucfirst($c) ?>
        </button>
        <?php endforeach; ?>
    </div>

    <?php $count = mysqli_num_rows($products); ?>
    <p style="color:#888;font-size:.85rem;margin-bottom:1.5rem">
        <?= $count ?> item<?= $count !== 1 ? 's' : '' ?> found
        <?= $search ? ' for &ldquo;<strong>' . htmlspecialchars($search) . '</strong>&rdquo;' : '' ?>
    </p>

    <?php if ($count === 0): ?>
        <div class="empty-state">
            <i class="fas fa-box-open"></i>
            <h3>No items found</h3>
            <p>Try a different search or category.</p>
        </div>
    <?php else: ?>
        <div class="product-grid">
            <?php while ($p = mysqli_fetch_assoc($products)): ?>
            <div class="product-card">
                <?php if ($p['status'] === 'reserved'): ?>
                    <span class="product-badge">Reserved</span>
                <?php endif; ?>
                <div class="product-img"><?= htmlspecialchars($p['emoji']) ?></div>
                <div class="product-info">
                    <p class="product-category"><?= ucfirst(htmlspecialchars($p['category'])) ?></p>
                    <h3 class="product-name"><?= htmlspecialchars($p['title']) ?></h3>
                    <p class="product-seller">
                        by <?= htmlspecialchars($p['seller']) ?>
                        <?php if($p['size']): ?> &middot; Size <?= htmlspecialchars($p['size']) ?><?php endif; ?>
                        &middot; <?= htmlspecialchars($p['condition_q']) ?>
                    </p>
                    <?php if (!empty($p['description'])): ?>
                    <p style="font-size:.82rem;color:#888;margin-bottom:.6rem;line-height:1.4">
                        <?= htmlspecialchars(mb_substr($p['description'], 0, 70)) ?>…
                    </p>
                    <?php endif; ?>
                    <p class="product-price">$<?= number_format($p['price'],2) ?></p>
                    <div class="product-actions">
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <?php if ($p['seller_id'] != $_SESSION['user_id']): ?>
                                <form method="POST" action="cart_action.php" style="flex:1">
                                    <input type="hidden" name="product_id" value="<?= (int)$p['id'] ?>">
                                    <input type="hidden" name="action" value="add">
                                    <button class="btn-cart" type="submit">🛒 Cart</button>
                                </form>
                                <form method="POST" action="reserve.php">
                                    <input type="hidden" name="product_id" value="<?= (int)$p['id'] ?>">
                                    <button class="btn-reserve" type="submit" title="Reserve for 24h">📌</button>
                                </form>
                            <?php else: ?>
                                <span style="font-size:.82rem;color:#aaa;flex:1;padding:.6rem;text-align:center">Your listing</span>
                            <?php endif; ?>
                        <?php else: ?>
                            <a href="login.php" class="btn-cart" style="text-align:center;display:block;flex:1">Login to Buy</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
