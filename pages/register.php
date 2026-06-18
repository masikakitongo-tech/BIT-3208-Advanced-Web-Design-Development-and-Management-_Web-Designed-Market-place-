<?php
// ============================================================
// FILE: pages/register.php
// PURPOSE: User registration with PHP validation & hashing
// ============================================================
require_once '../includes/db.php';
require_once '../includes/auth.php';
redirectIfLoggedIn();
$pageTitle = 'Register';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // FIX: do NOT escape before validation — escape happens in the query via prepared statements
    $name  = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email']     ?? '');
    $phone = trim($_POST['phone']     ?? '');
    $pass  = $_POST['password']  ?? '';
    $pass2 = $_POST['password2'] ?? '';

    if (!$name || !$email || !$pass) {
        $error = 'All required fields must be filled in.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($pass) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($pass !== $pass2) {
        $error = 'Passwords do not match.';
    } else {
        // FIX: use prepared statement for duplicate-email check
        $stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, 's', $email);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        if (mysqli_stmt_num_rows($stmt) > 0) {
            $error = 'An account with that email already exists.';
        } else {
            $hashed = password_hash($pass, PASSWORD_DEFAULT);
            // FIX: prepared statement for INSERT
            $ins = mysqli_prepare($conn,
                "INSERT INTO users (full_name, email, password, phone) VALUES (?, ?, ?, ?)");
            mysqli_stmt_bind_param($ins, 'ssss', $name, $email, $hashed, $phone);
            if (mysqli_stmt_execute($ins)) {
                // FIX: regenerate session ID on registration
                session_regenerate_id(true);
                $_SESSION['user_id']   = mysqli_insert_id($conn);
                $_SESSION['user_name'] = $name;
                header('Location: dashboard.php');
                exit();
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
    }
}
include '../includes/header.php';
?>
<div class="auth-page">
    <div class="auth-card fade-up">
        <h2>Create Account</h2>
        <p class="subtitle">Join ThreadHaven — buy &amp; sell fashion you love.</p>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" id="registerForm" novalidate>
            <div class="form-group">
                <label for="full_name">Full Name *</label>
                <input type="text" id="full_name" name="full_name"
                       placeholder="Jane Doe"
                       value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>"
                       required autocomplete="name">
            </div>
            <div class="form-group">
                <label for="email">Email Address *</label>
                <input type="email" id="email" name="email"
                       placeholder="jane@example.com"
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                       required autocomplete="email">
            </div>
            <div class="form-group">
                <label for="phone">Phone Number (optional)</label>
                <input type="tel" id="phone" name="phone"
                       placeholder="07XX XXX XXX"
                       value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>"
                       autocomplete="tel">
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="password">Password *</label>
                    <input type="password" id="password" name="password"
                           placeholder="Min. 6 characters" required autocomplete="new-password">
                    <small id="pwStrength" style="font-size:.78rem;margin-top:.3rem;display:block"></small>
                </div>
                <div class="form-group">
                    <label for="password2">Confirm Password *</label>
                    <input type="password" id="password2" name="password2"
                           placeholder="Repeat password" required autocomplete="new-password">
                </div>
            </div>
            <button type="submit" class="btn-submit">Create My Account</button>
        </form>
        <p class="auth-switch">Already have an account? <a href="login.php">Log in here</a></p>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
