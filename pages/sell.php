<?php
// ============================================================
// FILE: pages/sell.php
// PURPOSE: Form to list a new item for sale
// ============================================================
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireLogin();
$pageTitle = 'Sell an Item';
$error = $success = '';

$emojiMap = [
    'sweaters'=>'🧶','hoodies'=>'🧥','pants'=>'👖','jorts'=>'🩳','other'=>'👕'
];

// Allowed values for ENUM fields
$allowedCategories = ['sweaters','hoodies','pants','jorts','other'];
$allowedSizes      = ['','XS','S','M','L','XL','XXL','28','30','32','34','36'];
$allowedConditions = ['New','Like New','Good','Fair'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // FIX: use prepared statements; validate ENUMs server-side
    $title    = trim($_POST['title']       ?? '');
    $desc     = trim($_POST['description'] ?? '');
    $price    = floatval($_POST['price']   ?? 0);
    $category = $_POST['category']    ?? 'other';
    $size     = $_POST['size']         ?? '';
    $cond     = $_POST['condition_q']  ?? 'Good';

    // Whitelist ENUM values
    if (!in_array($category, $allowedCategories)) $category = 'other';
    if (!in_array($size,     $allowedSizes))       $size     = '';
    if (!in_array($cond,     $allowedConditions))  $cond     = 'Good';

    $emoji = $emojiMap[$category] ?? '👕';
    $uid   = (int)$_SESSION['user_id'];

    if (!$title) {
        $error = 'Item title is required.';
    } elseif ($price <= 0) {
        $error = 'Please enter a valid price greater than $0.';
    } else {
        // FIX: prepared statement for INSERT
        $stmt = mysqli_prepare($conn,
            "INSERT INTO products (seller_id, title, description, price, category, size, condition_q, emoji)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, 'issdssss', $uid, $title, $desc, $price, $category, $size, $cond, $emoji);
        if (mysqli_stmt_execute($stmt)) {
            $success = 'Your item has been listed! <a href="shop.php">View in shop &rarr;</a>';
        } else {
            $error = 'Could not list item. Please try again.';
        }
    }
}
include '../includes/header.php';
?>
<div class="sell-page">
    <h1>List an Item</h1>
    <p class="sub">Fill in the details below to add your item to the ThreadHaven marketplace.</p>

    <div class="sell-card">
        <?php if ($error):   ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>

        <form method="POST" id="sellForm" novalidate>
            <div class="form-group">
                <label for="title">Item Title *</label>
                <input type="text" id="title" name="title"
                       placeholder="e.g. Vintage Cream Hoodie Size M"
                       value="<?= htmlspecialchars($_POST['title'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description"
                          placeholder="Describe the item — condition, material, why you're selling..."><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="category">Category *</label>
                    <select id="category" name="category">
                        <option value="sweaters" <?= (($_POST['category']??'')=='sweaters')?'selected':'' ?>>🧶 Sweaters</option>
                        <option value="hoodies"  <?= (($_POST['category']??'hoodies')=='hoodies')?'selected':'' ?>>🧥 Hoodies</option>
                        <option value="pants"    <?= (($_POST['category']??'')=='pants')?'selected':'' ?>>👖 Pants</option>
                        <option value="jorts"    <?= (($_POST['category']??'')=='jorts')?'selected':'' ?>>🩳 Jorts</option>
                        <option value="other"    <?= (($_POST['category']??'')=='other')?'selected':'' ?>>👕 Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="size">Size</label>
                    <select id="size" name="size">
                        <option value="">— Select —</option>
                        <?php foreach (['XS','S','M','L','XL','XXL','28','30','32','34','36'] as $s): ?>
                            <option <?= (($_POST['size']??'')===$s)?'selected':'' ?>><?= $s ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="price">Asking Price (USD) *</label>
                    <input type="number" id="price" name="price"
                           placeholder="0.00" step="0.01" min="0.01"
                           value="<?= htmlspecialchars($_POST['price'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label for="condition_q">Condition</label>
                    <select id="condition_q" name="condition_q">
                        <?php foreach (['New','Like New','Good','Fair'] as $c): ?>
                            <option <?= (($_POST['condition_q']??'Like New')===$c)?'selected':'' ?>><?= $c ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <button type="submit" class="btn-submit">🏷️ Publish Listing</button>
        </form>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
