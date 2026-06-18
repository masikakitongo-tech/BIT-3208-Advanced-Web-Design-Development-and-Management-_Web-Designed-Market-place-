<?php
// ============================================================
// FILE: pages/login.php
// PURPOSE: User login with session creation
// ============================================================
require_once '../includes/db.php';
require_once '../includes/auth.php';
redirectIfLoggedIn();
$pageTitle = 'Login';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // FIX: use prepared statements instead of string interpolation
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';

    if (!$email || !$pass) {
        $error = 'Please fill in both fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        $stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE email = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, 's', $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user   = mysqli_fetch_assoc($result);

        if ($user && password_verify($pass, $user['password'])) {
            // FIX: regenerate session ID on login to prevent session fixation
            session_regenerate_id(true);
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_name'] = $user['full_name'];
            header('Location: dashboard.php');
            exit();
        } else {
            $error = 'Invalid email or password. Please try again.';
        }
    }
}
include '../includes/header.php';
?>
<div class="auth-page">
    <div class="auth-card fade-up">
        <h2>Welcome Back</h2>
        <p class="subtitle">Log into your ThreadHaven account.</p>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if (isset($_GET['registered'])): ?>
            <div class="alert alert-success">Account created! Please log in.</div>
        <?php endif; ?>

        <form method="POST" id="loginForm" novalidate>
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email"
                       placeholder="jane@example.com"
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                       required autocomplete="email">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password"
                       placeholder="Your password" required autocomplete="current-password">
            </div>
            <button type="submit" class="btn-submit">Log In</button>
        </form>
        <p class="auth-switch">Don't have an account? <a href="register.php">Register here</a></p>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
