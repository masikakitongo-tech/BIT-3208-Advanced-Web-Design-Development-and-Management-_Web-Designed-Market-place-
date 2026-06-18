<?php
// ============================================================
// FILE: index.php  (root — place in htdocs/marketplace/)
// PURPOSE: Homepage with hero, featured products, mockup
// BIT3208 - Advanced Web Design and Development
// ============================================================
require_once 'includes/db.php';
require_once 'includes/auth.php';
$pageTitle = 'Home';
include 'includes/header.php';

// Fetch 4 latest available products
$featured = mysqli_query($conn,
    "SELECT p.*, u.full_name as seller
     FROM products p JOIN users u ON p.seller_id = u.id
     WHERE p.status = 'available'
     ORDER BY p.created_at DESC LIMIT 4");
?>

<!-- ── HERO ─────────────────────────────────────────────── -->
<section class="hero">
    <div class="hero-inner">
        <div class="hero-text fade-up">
            <p class="hero-eyebrow">🧵 Buy &amp; Sell Fashion</p>
            <h1>Your wardrobe's<br><em>next chapter</em><br>starts here.</h1>
            <p>ThreadHaven is the marketplace for fashion lovers. Discover unique hoodies, sweaters, pants &amp; jorts from real people — or clear out your closet.</p>
            <div class="hero-cta">
                <a href="pages/shop.php" class="btn-primary"><i class="fas fa-store"></i> Browse Shop</a>
                <?php if (!isset($_SESSION['user_id'])): ?>
                    <a href="pages/register.php" class="btn-secondary"><i class="fas fa-user-plus"></i> Start Selling</a>
                <?php else: ?>
                    <a href="pages/sell.php" class="btn-secondary"><i class="fas fa-plus"></i> List an Item</a>
                <?php endif; ?>
            </div>
        </div>
        <div class="hero-visual fade-up-2">
            <div class="hero-card featured">
                <div class="hero-card-emoji">🛍️</div>
                <h3>100+ Listings</h3>
                <p>New items added daily</p>
            </div>
            <div class="hero-card">
                <div class="hero-card-emoji">🧥</div>
                <h3>Hoodies</h3><p>Trending this week</p>
            </div>
            <div class="hero-card">
                <div class="hero-card-emoji">👖</div>
                <h3>Pants &amp; Jorts</h3><p>All sizes</p>
            </div>
        </div>
    </div>
</section>

<!-- ── FEATURED PRODUCTS ─────────────────────────────────── -->
<section class="section">
    <div class="section-inner">
        <div class="section-header">
            <span class="section-label">Fresh Drops</span>
            <h2>Latest Listings</h2>
            <p>Hand-picked from our community — new arrivals every day.</p>
        </div>
        <div class="product-grid">
            <?php while ($p = mysqli_fetch_assoc($featured)): ?>
            <div class="product-card">
                <?php if ($p['status'] === 'reserved'): ?>
                    <span class="product-badge">Reserved</span>
                <?php endif; ?>
                <div class="product-img"><?= htmlspecialchars($p['emoji']) ?></div>
                <div class="product-info">
                    <p class="product-category"><?= ucfirst($p['category']) ?></p>
                    <h3 class="product-name"><?= htmlspecialchars($p['title']) ?></h3>
                    <p class="product-seller">by <?= htmlspecialchars($p['seller']) ?>
                        <?php if($p['size']): ?> · Size <?= htmlspecialchars($p['size']) ?><?php endif; ?>
                    </p>
                    <p class="product-price">$<?= number_format($p['price'],2) ?></p>
                    <div class="product-actions">
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <form method="POST" action="pages/cart_action.php" style="flex:1">
                                <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                                <input type="hidden" name="action" value="add">
                                <button class="btn-cart" type="submit">🛒 Add to Cart</button>
                            </form>
                            <form method="POST" action="pages/reserve.php">
                                <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                                <button class="btn-reserve" type="submit" title="Reserve">📌</button>
                            </form>
                        <?php else: ?>
                            <a href="pages/login.php" class="btn-cart" style="text-align:center;display:block;flex:1">Login to Buy</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
        <div class="text-center mt-2">
            <a href="pages/shop.php" class="btn-primary">View All Items <i class="fas fa-arrow-right"></i></a>
        </div>
    </div>
</section>

<!-- ── HOW IT WORKS ──────────────────────────────────────── -->
<section class="section" style="background:var(--mist)">
    <div class="section-inner">
        <div class="section-header">
            <span class="section-label">Simple Process</span>
            <h2>How ThreadHaven Works</h2>
            <p>Buy and sell in just a few clicks.</p>
        </div>
        <div class="steps-grid">
            <div class="step-card">
                <div class="step-num">1</div>
                <div class="step-icon">📝</div>
                <h3>Create Account</h3>
                <p>Register for free and set up your profile in under a minute.</p>
            </div>
            <div class="step-card">
                <div class="step-num">2</div>
                <div class="step-icon">🔍</div>
                <h3>Browse &amp; Discover</h3>
                <p>Search by category, size, or price. Filter exactly what you need.</p>
            </div>
            <div class="step-card">
                <div class="step-num">3</div>
                <div class="step-icon">🛒</div>
                <h3>Add to Cart</h3>
                <p>Add items, reserve them, or proceed straight to checkout.</p>
            </div>
            <div class="step-card">
                <div class="step-num">4</div>
                <div class="step-icon">💰</div>
                <h3>Sell Your Clothes</h3>
                <p>List your items from your dashboard. Reach buyers instantly.</p>
            </div>
        </div>
    </div>
</section>

<!-- ── MOBILE MOCKUP ─────────────────────────────────────── -->
<!-- LOGBOOK EVIDENCE: Week 2 - Fig 3: Mobile View Mockup -->
<section class="mockup-section">
    <div class="mockup-inner">
        <div class="mockup-text">
            <span class="section-label">Mobile First Design</span>
            <h2>Shop anywhere,<br>on any device.</h2>
            <p>ThreadHaven is fully responsive — the same clean experience whether you're browsing on a laptop, tablet, or phone.</p>
            <ul class="feature-list">
                <li><i class="fas fa-check-circle"></i> Mobile-optimised navigation</li>
                <li><i class="fas fa-check-circle"></i> Touch-friendly cart &amp; checkout</li>
                <li><i class="fas fa-check-circle"></i> Fast product browsing on small screens</li>
                <li><i class="fas fa-check-circle"></i> Seller dashboard on the go</li>
            </ul>
        </div>
        <!-- Phone wireframe mockup -->
        <div class="phone-frame">
            <div class="phone-notch"></div>
            <div class="phone-screen">
                <div class="phone-nav">
                    <span class="logo">🧵 ThreadHaven</span>
                    <span class="cart-icon"><i class="fas fa-shopping-bag"></i></span>
                </div>
                <div class="phone-hero">
                    <h3>Spring Drops 🌿</h3>
                    <p>Fresh listings added today</p>
                    <span class="ph-btn">Shop Now</span>
                </div>
                <div class="phone-products">
                    <h4>New Arrivals</h4>
                    <div class="ph-product-list">
                        <div class="ph-product">
                            <div class="ph-product-img">🧥</div>
                            <div>
                                <div class="ph-product-name">Cream Hoodie</div>
                                <div class="ph-product-price">$28.00</div>
                            </div>
                            <button class="ph-product-cart">+Cart</button>
                        </div>
                        <div class="ph-product">
                            <div class="ph-product-img">👖</div>
                            <div>
                                <div class="ph-product-name">Slim Jeans</div>
                                <div class="ph-product-price">$22.00</div>
                            </div>
                            <button class="ph-product-cart">+Cart</button>
                        </div>
                        <div class="ph-product">
                            <div class="ph-product-img">🧶</div>
                            <div>
                                <div class="ph-product-name">Knit Sweater</div>
                                <div class="ph-product-price">$40.00</div>
                            </div>
                            <button class="ph-product-cart">+Cart</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="phone-bottom-nav">
                <div class="ph-nav-item active"><i class="fas fa-home"></i><span>Home</span></div>
                <div class="ph-nav-item"><i class="fas fa-search"></i><span>Shop</span></div>
                <div class="ph-nav-item"><i class="fas fa-shopping-bag"></i><span>Cart</span></div>
                <div class="ph-nav-item"><i class="fas fa-user"></i><span>Profile</span></div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
