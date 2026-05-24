<?php
$rootPath = '../';
require_once '../config/db.php';

if (isset($_SESSION['user_id'])) {
    header("Location: ../dashboard.php");
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    if (empty($name) || empty($email) || empty($password) || empty($confirm)) {
        $error = 'Please fill in all fields.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } else {
        $checkStmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $checkStmt->execute([$email]);

        if ($checkStmt->fetch()) {
            $error = 'Email already registered.';
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            try {
                $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
                $stmt->execute([$name, $email, $hashed]);
                $success = 'Account created! You can now login.';
            } catch (PDOException $e) {
                $error = 'Error creating account: ' . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register — SleepTrack</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<div class="auth-page">
    <div class="auth-box">
        <div class="auth-logo"><span>🌙</span> SleepTrack</div>
        <div class="auth-sub">Track your sleep, improve your life</div>
        <h2 class="auth-title">Create Account</h2>

        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <p style="text-align: center; margin-bottom: 1rem;"><a href="login.php" style="color: var(--accent2);">Go to login</a></p>
        <?php else: ?>
            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="name" placeholder="Your name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required autofocus>
                </div>
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" placeholder="you@example.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" placeholder="At least 6 characters" required>
                </div>
                <div class="form-group">
                    <label>Confirm Password</label>
                    <input type="password" name="confirm_password" placeholder="Confirm your password" required>
                </div>
                <button type="submit" class="btn btn-primary">Create Account</button>
            </form>

            <div class="auth-footer">
                Already have an account? <a href="login.php">Sign in</a>
            </div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>