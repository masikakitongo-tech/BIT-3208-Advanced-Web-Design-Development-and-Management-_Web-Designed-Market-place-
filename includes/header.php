<?php
// ============================================================
// FILE: includes/header.php
// PURPOSE: Shared HTML head and navigation bar
// ============================================================
if (session_status() === PHP_SESSION_NONE) session_start();

// FIX: guard against $conn not being set (e.g. called before db.php)
$cartCount = 0;
if (isset($_SESSION['user_id']) && isset($conn)) {
    $uid = (int)$_SESSION['user_id'];
    $cr  = mysqli_query($conn, "SELECT SUM(quantity) as t FROM cart WHERE user_id=$uid");
    if ($cr) {
        $crow      = mysqli_fetch_assoc($cr);
        $cartCount = (int)($crow['t'] ?? 0);
    }
}

$currentPage = basename($_SERVER['PHP_SELF']);

// Determine the web root of the marketplace folder (works from any depth)
$_base = rtrim(str_replace('\\','/', dirname(dirname($_SERVER['PHP_SELF']))), '/');
// If index.php is at /marketplace/index.php, dirname gives /marketplace
// If a page is at /marketplace/pages/login.php, dirname(dirname()) gives /marketplace
$_mktBase = '/marketplace';  // ← hardcoded to match your XAMPP folder name
$cssPath  = $_mktBase . '/css/style.css';
$jsPath   = $_mktBase . '/assets/main.js';
$depth    = (basename(dirname($_SERVER['PHP_SELF'])) === 'marketplace') ? '' : '../';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'ThreadHaven') ?> | ThreadHaven Marketplace</title>
    <link rel="stylesheet" href="<?= $cssPath ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@400;700;900&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<nav class="navbar">
    <div class="nav-container">
        <a href="<?= $depth ?>index.php" class="nav-logo">
            <span class="logo-icon">🧵</span>
            <span class="logo-text">ThreadHaven</span>
        </a>

        <button class="hamburger" id="hamburger" aria-label="Menu">
            <span></span><span></span><span></span>
        </button>

        <ul class="nav-links" id="navLinks">
            <li><a href="<?= $depth ?>index.php" class="<?= $currentPage==='index.php'?'active':'' ?>">Home</a></li>
            <li><a href="<?= $depth ?>pages/shop.php" class="<?= $currentPage==='shop.php'?'active':'' ?>">Shop</a></li>
            <?php if (isset($_SESSION['user_id'])): ?>
                <li><a href="<?= $depth ?>pages/dashboard.php" class="<?= $currentPage==='dashboard.php'?'active':'' ?>">Dashboard</a></li>
                <li><a href="<?= $depth ?>pages/sell.php" class="btn-sell <?= $currentPage==='sell.php'?'active':'' ?>">+ Sell</a></li>
                <li>
                    <a href="<?= $depth ?>pages/cart.php" class="cart-link <?= $currentPage==='cart.php'?'active':'' ?>">
                        <i class="fas fa-shopping-bag"></i>
                        <?php if ($cartCount > 0): ?>
                            <span class="cart-badge"><?= $cartCount ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <li><a href="<?= $depth ?>pages/logout.php" class="nav-logout">Logout</a></li>
            <?php else: ?>
                <li><a href="<?= $depth ?>pages/login.php" class="<?= $currentPage==='login.php'?'active':'' ?>">Login</a></li>
                <li><a href="<?= $depth ?>pages/register.php" class="btn-register">Register</a></li>
            <?php endif; ?>
        </ul>
    </div>
</nav>
