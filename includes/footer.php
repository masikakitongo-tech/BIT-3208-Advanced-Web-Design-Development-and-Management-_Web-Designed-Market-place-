<!-- ============================================================
     FILE: includes/footer.php
     PURPOSE: Shared footer and closing HTML tags
     ============================================================ -->

<?php
// Determine link prefix for footer: pages in /pages/ need no prefix; root needs 'pages/'
$depth = (basename(dirname($_SERVER['PHP_SELF'])) === 'marketplace') ? 'pages/' : '';
?>

<footer class="site-footer">
    <div class="footer-inner">
        <div class="footer-brand">
            <span class="logo-icon">🧵</span>
            <span class="logo-text">ThreadHaven</span>
            <p>Buy & sell fashion you love.</p>
        </div>
        <div class="footer-links">
            <h4>Shop</h4>
            <a href="<?= $depth ?>shop.php">All Items</a>
            <a href="<?= $depth ?>shop.php?cat=sweaters">Sweaters</a>
            <a href="<?= $depth ?>shop.php?cat=hoodies">Hoodies</a>
            <a href="<?= $depth ?>shop.php?cat=pants">Pants</a>
            <a href="<?= $depth ?>shop.php?cat=jorts">Jorts</a>
        </div>
        <div class="footer-links">
            <h4>Account</h4>
            <a href="<?= $depth ?>login.php">Login</a>
            <a href="<?= $depth ?>register.php">Register</a>
            <a href="<?= $depth ?>dashboard.php">Dashboard</a>
            <a href="<?= $depth ?>sell.php">Sell an Item</a>
        </div>
        <div class="footer-links">
            <h4>ThreadHaven</h4>
            <p style="font-size:.8rem;opacity:.6">Fashion Marketplace<br>Buy &amp; Sell<br>Community Project</p>
        </div>
    </div>
    <div class="footer-bottom">
        <p>&copy; <?= date('Y') ?> ThreadHaven Marketplace</p>
    </div>
</footer>

<script src="/marketplace/assets/main.js"></script>
</body>
</html>
